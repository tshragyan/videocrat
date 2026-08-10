<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Shopify\Exception\MissingArgumentException;
use Symfony\Component\HttpFoundation\Response;

class ShopifyTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (env('APP_ENV') !== 'local') {
            if (!$request->headers->has('authorization')) {
                throw new MissingArgumentException('Missing Authorization key in headers array');
            }

            $auth = $request->headers->get('authorization');
            preg_match('/^Bearer (.+)$/', (string) $auth, $matches);
            if (!$matches) {
                throw new MissingArgumentException('Missing Bearer token in authorization header');
            }

            JWT::$leeway = 10;
            $payload = JWT::decode($matches[1], new Key(config('services.shopify.client_secret'), 'HS256'));
            $shopName = explode('tps://', $payload->dest)[1];
            /** @var User $user */
            $user = User::query()->where('shopify_username', '=', $shopName)->first();

            if ($user) {
                auth()->login($user);
            }
        } else {
            /** @var User $user */
            $user = User::query()->first();
            auth()->login($user);
        }

        return $next($request);
    }
}
