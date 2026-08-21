<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class InstagramVideoImporter
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'timeout' => 300,
            'connect_timeout' => 30,
            'http_errors' => false,
            'allow_redirects' => true,
        ]);
    }

    /**
     * Import Instagram video directly into Shopify.
     *
     * Flow:
     *
     * 1. HEAD Instagram URL
     * 2. Get Content-Length and Content-Type
     * 3. Create Shopify staged upload
     * 4. Stream Instagram video directly to Shopify
     * 5. Create permanent Shopify File
     * 6. Wait until Shopify processing is READY
     * 7. Return Shopify file ID
     *
     * No MP4 is saved on the Laravel server.
     */
    public function import(
        string $instagramUrl,
        string $shopDomain,
        string $shopifyAccessToken
    ): array {
        try {

            /*
             * =========================================================
             * 1. CHECK INSTAGRAM VIDEO
             * =========================================================
             *
             * HEAD request only.
             *
             * We DON'T download the video here.
             */

            $headResponse = $this->http->head($instagramUrl, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0',
                    'Accept' => 'video/mp4,video/*,*/*',
                ],
            ]);

            $status = $headResponse->getStatusCode();

            $contentType = strtolower(
                $headResponse->getHeaderLine('Content-Type')
            );

            $contentLength = $headResponse->getHeaderLine(
                'Content-Length'
            );

            if ($status < 200 || $status >= 300) {
                throw new RuntimeException(
                    "Instagram returned HTTP {$status}"
                );
            }

            if (!str_starts_with($contentType, 'video/')) {
                throw new RuntimeException(
                    "Instagram URL is not a video. Content-Type: {$contentType}"
                );
            }

            if (
                !is_numeric($contentLength) ||
                (int) $contentLength <= 0
            ) {
                throw new RuntimeException(
                    "Instagram did not return valid Content-Length: {$contentLength}"
                );
            }

            $fileSize = (int) $contentLength;

            /*
             * Shopify requires a filename.
             *
             * Do NOT use the huge Instagram URL as filename.
             */

            $filename = 'instagram-' . uniqid('', true) . '.mp4';

            /*
             * =========================================================
             * 2. SHOPIFY GRAPHQL URL
             * =========================================================
             */

            $graphqlUrl =
                "https://{$shopDomain}/admin/api/2026-07/graphql.json";

            /*
             * =========================================================
             * 3. CREATE STAGED UPLOAD
             * =========================================================
             */

            $stagedMutation = <<<'GRAPHQL'
mutation stagedUploadsCreate($input: [StagedUploadInput!]!) {
    stagedUploadsCreate(input: $input) {
        stagedTargets {
            url
            resourceUrl
            parameters {
                name
                value
            }
        }

        userErrors {
            field
            message
        }
    }
}
GRAPHQL;

            $stagedResponse = Http::withHeaders([
                'X-Shopify-Access-Token' => $shopifyAccessToken,
                'Content-Type' => 'application/json',
            ])->post($graphqlUrl, [
                'query' => $stagedMutation,

                'variables' => [
                    'input' => [
                        [
                            'filename' => $filename,

                            'mimeType' => 'video/mp4',

                            'fileSize' => (string) $fileSize,

                            'httpMethod' => 'POST',

                            'resource' => 'VIDEO',
                        ],
                    ],
                ],
            ]);

            if (!$stagedResponse->successful()) {
                throw new RuntimeException(
                    'Shopify stagedUploadsCreate HTTP error: ' .
                    $stagedResponse->status() .
                    ' ' .
                    $stagedResponse->body()
                );
            }

            $stagedJson = $stagedResponse->json();

            /*
             * GraphQL errors
             */

            if (!empty($stagedJson['errors'])) {
                throw new RuntimeException(
                    'Shopify GraphQL error: ' .
                    json_encode(
                        $stagedJson['errors'],
                        JSON_UNESCAPED_UNICODE
                    )
                );
            }

            $stagedData =
                $stagedJson['data']['stagedUploadsCreate']
                ?? null;

            if (!$stagedData) {
                throw new RuntimeException(
                    'Invalid stagedUploadsCreate response.'
                );
            }

            /*
             * Shopify user errors
             */

            if (!empty($stagedData['userErrors'])) {
                throw new RuntimeException(
                    'Shopify staged upload error: ' .
                    json_encode(
                        $stagedData['userErrors'],
                        JSON_UNESCAPED_UNICODE
                    )
                );
            }

            $target =
                $stagedData['stagedTargets'][0]
                ?? null;

            if (!$target) {
                throw new RuntimeException(
                    'Shopify did not return staged upload target.'
                );
            }

            $uploadUrl =
                $target['url']
                ?? null;

            $resourceUrl =
                $target['resourceUrl']
                ?? null;

            $parameters =
                $target['parameters']
                ?? [];

            if (!$uploadUrl || !$resourceUrl) {
                throw new RuntimeException(
                    'Shopify staged target is missing URL or resourceUrl.'
                );
            }

            /*
             * =========================================================
             * 4. STREAM INSTAGRAM -> SHOPIFY
             * =========================================================
             *
             * IMPORTANT:
             *
             * We don't save the video to disk.
             *
             * Instagram response body is streamed directly into
             * Shopify's staged upload request.
             */

            $instagramResponse = $this->http->get(
                $instagramUrl,
                [
                    'stream' => true,

                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0',

                        'Accept' => 'video/mp4,video/*,*/*',

                        'Connection' => 'keep-alive',
                    ],
                ]
            );

            $instagramStatus =
                $instagramResponse->getStatusCode();

            if (
                $instagramStatus < 200 ||
                $instagramStatus >= 300
            ) {
                throw new RuntimeException(
                    "Unable to download Instagram stream. HTTP {$instagramStatus}"
                );
            }

            /*
             * Verify that Instagram actually returned video.
             */

            $streamContentType =
                strtolower(
                    $instagramResponse->getHeaderLine(
                        'Content-Type'
                    )
                );

            if (
                $streamContentType !== '' &&
                !str_starts_with(
                    $streamContentType,
                    'video/'
                )
            ) {
                throw new RuntimeException(
                    "Instagram stream is not a video. Content-Type: {$streamContentType}"
                );
            }

            /*
             * Build Shopify multipart parameters.
             */

            $multipart = [];

            foreach ($parameters as $parameter) {

                $multipart[] = [
                    'name' =>
                        $parameter['name'],

                    'contents' =>
                        $parameter['value'],
                ];
            }

            /*
             * IMPORTANT:
             *
             * The contents is the STREAM coming from Instagram.
             *
             * No local MP4 file.
             */

            $multipart[] = [
                'name' => 'file',

                'contents' =>
                    $instagramResponse->getBody(),

                'filename' =>
                    $filename,

                'headers' => [
                    'Content-Type' => 'video/mp4',
                ],
            ];

            /*
             * Send directly to Shopify staging.
             */

            $uploadResponse = $this->http->post(
                $uploadUrl,
                [
                    'multipart' => $multipart,

                    'timeout' => 300,

                    'connect_timeout' => 30,

                    'http_errors' => false,
                ]
            );

            $uploadStatus =
                $uploadResponse->getStatusCode();

            /*
             * Shopify normally returns:
             *
             * 204 No Content
             *
             * This means SUCCESS.
             */

            if (
            !in_array(
                $uploadStatus,
                [200, 201, 204],
                true
            )
            ) {
                $body =
                    $uploadResponse
                        ->getBody()
                        ->getContents();

                throw new RuntimeException(
                    'Shopify staged upload failed. HTTP ' .
                    $uploadStatus .
                    '. Body: ' .
                    $body
                );
            }

            /*
             * =========================================================
             * 5. CREATE PERMANENT SHOPIFY FILE
             * =========================================================
             */

            $fileCreateMutation = <<<'GRAPHQL'
mutation fileCreate($files: [FileCreateInput!]!) {
    fileCreate(files: $files) {
        files {
            id
            fileStatus
            alt
            createdAt

            ... on Video {
                id

                sources {
                    url
                    mimeType
                    format
                    width
                    height
                }
            }
        }

        userErrors {
            field
            message
        }
    }
}
GRAPHQL;

            $fileCreateResponse = Http::withHeaders([
                'X-Shopify-Access-Token' => $shopifyAccessToken,
                'Content-Type' => 'application/json',
            ])->post($graphqlUrl, [
                'query' => $fileCreateMutation,

                'variables' => [
                    'files' => [
                        [
                            /*
                             * IMPORTANT:
                             *
                             * This is NOT Instagram URL.
                             *
                             * This is Shopify resourceUrl.
                             */

                            'originalSource' =>
                                $resourceUrl,

                            'contentType' =>
                                'VIDEO',

                            'alt' =>
                                'Instagram video',
                        ],
                    ],
                ],
            ]);

            if (!$fileCreateResponse->successful()) {
                throw new RuntimeException(
                    'Shopify fileCreate HTTP error: ' .
                    $fileCreateResponse->status() .
                    ' ' .
                    $fileCreateResponse->body()
                );
            }

            $fileCreateJson =
                $fileCreateResponse->json();

            /*
             * GraphQL errors
             */

            if (!empty($fileCreateJson['errors'])) {
                throw new RuntimeException(
                    'Shopify GraphQL fileCreate error: ' .
                    json_encode(
                        $fileCreateJson['errors'],
                        JSON_UNESCAPED_UNICODE
                    )
                );
            }

            $fileCreateData =
                $fileCreateJson['data']['fileCreate']
                ?? null;

            if (!$fileCreateData) {
                throw new RuntimeException(
                    'Invalid fileCreate response.'
                );
            }

            /*
             * Shopify user errors
             */

            if (!empty($fileCreateData['userErrors'])) {
                throw new RuntimeException(
                    'Shopify fileCreate error: ' .
                    json_encode(
                        $fileCreateData['userErrors'],
                        JSON_UNESCAPED_UNICODE
                    )
                );
            }

            /*
             * Get created file.
             */

            $shopifyFile =
                $fileCreateData['files'][0]
                ?? null;

            if (!$shopifyFile) {
                throw new RuntimeException(
                    'Shopify did not return created file.'
                );
            }

            $shopifyFileId =
                $shopifyFile['id']
                ?? null;

            if (!$shopifyFileId) {
                throw new RuntimeException(
                    'Shopify file ID is missing.'
                );
            }

            /*
             * =========================================================
             * 6. WAIT FOR SHOPIFY PROCESSING
             * =========================================================
             */

            $readyFile =
                $this->waitUntilReady(
                    graphqlUrl: $graphqlUrl,
                    accessToken: $shopifyAccessToken,
                    fileId: $shopifyFileId
                );

            /*
             * =========================================================
             * 7. RETURN
             * =========================================================
             */

            return [
                'success' => true,

                'shopify_file_id' =>
                    $readyFile['id'],

                'file_status' =>
                    $readyFile['fileStatus'] ?? null,

                'alt' =>
                    $readyFile['alt'] ?? null,

                'sources' =>
                    $readyFile['sources'] ?? [],

                'resource_url' =>
                    $resourceUrl,

                'filename' =>
                    $filename,

                'file_size' =>
                    $fileSize,

                'file_size_mb' =>
                    round(
                        $fileSize / 1024 / 1024,
                        2
                    ),
            ];

        } catch (\Throwable $e) {

            Log::error(
                'Instagram video import failed',
                [
                    'message' =>
                        $e->getMessage(),

                    'instagram_url' =>
                        $instagramUrl,

                    'shop_domain' =>
                        $shopDomain,

                    'exception' =>
                        get_class($e),
                ]
            );

            return [
                'success' => false,

                'error' =>
                    $e->getMessage(),
            ];
        }
    }

    /**
     * Wait until Shopify finishes processing the video.
     */
    private function waitUntilReady(
        string $graphqlUrl,
        string $accessToken,
        string $fileId
    ): array {
        $query = <<<'GRAPHQL'
query getFile($id: ID!) {
    node(id: $id) {
        ... on MediaImage {
            id
            fileStatus
            alt
        }

        ... on Video {
            id
            fileStatus
            alt

            sources {
                url
                mimeType
                format
                width
                height
            }
        }
    }
}
GRAPHQL;

        /*
         * Maximum waiting time:
         *
         * 5 minutes
         *
         * 10 seconds between requests.
         */

        $maxAttempts = 30;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {

            $response = Http::withHeaders([
                'X-Shopify-Access-Token' =>
                    $accessToken,

                'Content-Type' =>
                    'application/json',
            ])->post(
                $graphqlUrl,
                [
                    'query' => $query,

                    'variables' => [
                        'id' => $fileId,
                    ],
                ]
            );

            if (!$response->successful()) {
                throw new RuntimeException(
                    'Shopify file status HTTP error: ' .
                    $response->status() .
                    ' ' .
                    $response->body()
                );
            }

            $json = $response->json();

            if (!empty($json['errors'])) {
                throw new RuntimeException(
                    'Shopify file status GraphQL error: ' .
                    json_encode(
                        $json['errors'],
                        JSON_UNESCAPED_UNICODE
                    )
                );
            }

            $file =
                $json['data']['node']
                ?? null;

            if (!$file) {
                throw new RuntimeException(
                    'Shopify file was not found: ' .
                    $fileId
                );
            }

            $status =
                $file['fileStatus']
                ?? null;

            /*
             * SUCCESS
             */

            if ($status === 'READY') {
                return $file;
            }

            /*
             * FAILED
             */

            if (
            in_array(
                $status,
                [
                    'FAILED',
                    'REJECTED',
                ],
                true
            )
            ) {
                throw new RuntimeException(
                    "Shopify video processing failed. Status: {$status}"
                );
            }

            /*
             * Still processing.
             */

            sleep(10);
        }

        throw new RuntimeException(
            'Shopify video processing timeout. File ID: ' .
            $fileId
        );
    }
}
