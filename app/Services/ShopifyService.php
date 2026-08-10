<?php


namespace App\Services;


use App\Models\ShopifyErrorLog;
use App\Models\User;
use Gnikyt\BasicShopifyAPI\BasicShopifyAPI;
use Gnikyt\BasicShopifyAPI\Options;
use Gnikyt\BasicShopifyAPI\Session;

class ShopifyService
{
    private string $appId;
    private User $user;
    private BasicShopifyAPI $api;

    public function __construct(User $user)
    {
        $this->appId = config('services.shopify.app_id');
        $options = (new Options())
            ->setVersion('2026-07')
            ->setType(false);
        $this->api = new BasicShopifyAPI($options);
        $session = new Session($user->getDomain(), $user->shopify_token);
        $this->api->setSession($session);
        $this->user = $user;
    }

    public function getStoreInfo()
    {
        $query = 'query ShopShow {
                      shop {
                        billingAddress {
                          address1
                          address2
                          city
                          company
                          country
                          countryCodeV2
                          latitude
                          longitude
                          phone
                          province
                          provinceCode
                          zip
                        }
                        contactEmail
                        createdAt
                        email
                        id
                        myshopifyDomain
                        name
                        url
                      }
                    }';
        $response = $this->api->graph($query);

        if ($response['errors']) {
            ShopifyErrorLog::query()->create([
                'usr_id' => $this->user->id,
                'method' => 'getStoreInfo',
                'data' => json_encode($response['errors'])
            ]);

            return [];
        }

        return $this->api->graph($query)['body']['container']['data']['shop'];
    }


}
