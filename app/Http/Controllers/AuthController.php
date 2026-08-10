<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    protected $apiKey;
    protected $apiSecret;
    protected $scopes;
    protected $redirectUri;

    public function __construct()
    {
        $this->apiKey = config('services.shopify.api_key');
        $this->apiSecret = config('services.shopify.client_secret');
        $this->scopes = config('services.shopify.shopify_scopes');
        $this->redirectUri = route('shopify.callback');
    }

    public function callback(Request $request)
    {
//        ShopifyErrorLog::query()->create(
//            [
//                'user_id' => 6,
//                'method' => 'callback',
//                'data' => json_encode($request->all()),
//            ]
//        );
        $shop = $request->get('shop');
        $hmac = $request->get('hmac');
        $code = $request->get('code');
        $state = $request->get('state');
        $host = $request->get('host');

        if (!$shop || !$hmac || !$code) {
            return response('Invalid request', 400);
        }

        $nonce = cache()->get($shop . '_state');
        if (!$nonce || $state !== $nonce) {
            return response('Invalid state ' . $nonce . '-' . $state, 403);
        }

        $params = $request->except(['hmac', 'signature', 'utf8']);
        ksort($params);
        $queryString = http_build_query($params);
        $calculated = hash_hmac('sha256', urldecode(http_build_query($params)), $this->apiSecret);
        if (!hash_equals($calculated, $hmac)) {
            return response('HMAC validation failed', 403);
        }

        $response = Http::asForm()->post("https://{$shop}/admin/oauth/access_token", [
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret,
            'code' => $code,
        ]);

        if (!$response->ok()) {
            return response('Failed to get access token: ' . $response->body(), 500);
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;
        $scope = $data['scope'] ?? null;

        if (!$accessToken) {
            return response('No access token returned', 500);
        }

        /** @var User $user */
        $user = User::query()->updateOrCreate(
            ['shopify_username' => $shop],
            [
                'shopify_token' => $accessToken,
                'role' => User::ROLES_USER,
                'email' => 'user@email.com' . rand(1, 9999),
                'shopify_id' => 1246654,
                'shopify_data' => '[]',
                'name' => 'user',
                'status' => User::STATUS_INACTIVE,
            ]
        );

        if (empty(json_decode($user->shopify_data))) {
            $shopData = $user->getService()->getStoreInfo();
            $user->shopify_data = json_encode($shopData);
            $user->shopify_id = $shopData['id'];
            $user->email = $shopData['email'];
            $user->name = $shopData['name'];
            $user->save();
        }

        auth()->login($user);
        session(['shopify_shop' => $shop]);
        $host = cache()->get('host_' . $shop);
//        ShopifyErrorLog::query()->create(
//            [
//                'user_id' => 6,
//                'method' => 'redirect to dashboard',
//                'data' => '{}',
//
//            ]
//        );
        return redirect()->to(route('dashboard.home', $request->all()));
    }

    public function install(Request $request)
    {
//        ShopifyErrorLog::query()->create(
//            [
//                'user_id' => 6,
//                'method' => 'install',
//                'data' => json_encode($request->all()),
//            ]
//        );

        $shop = $request->get('shop');
        if (!$shop) {

//            ShopifyErrorLog::query()->create(
//                [
//                    'user_id' => 6,
//                    'method' => 'install',
//                    'data' => 'missing shop parameter',
//                ]
//            );
            return response('Missing shop parameter', 400);
        }

        $nonce = bin2hex(random_bytes(16));
        cache()->put($shop . '_state', $nonce);
        cache()->put('host_' . $shop, $request->get('host'));
        $installUrl = "https://{$shop}/admin/oauth/authorize?" . http_build_query([
                'client_id' => $this->apiKey,
                'scope' => $this->scopes,
                'redirect_uri' => $this->redirectUri,
                'state' => $nonce,
            ]);



//        ShopifyErrorLog::query()->create(
//            [
//                'user_id' => 6,
//                'method' => 'install',
//                'data' => 'redirecting',
//            ]
//        );

        return redirect()->away($installUrl);
    }

    public function apiCallback(Request $request)
    {
        return true;
    }
}
