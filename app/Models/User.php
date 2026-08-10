<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\ShopifyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class User
 * @package App\Models
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $shopify_data
 * @property string $shopify_id
 * @property string $password
 * @property string $shopify_token
 * @property integer $status
 * @property string $shopify_username
 * @property string $remember_token
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLES_ADMIN = 1;
    const ROLES_USER = 2;
    const ROLES_SUPER_ADMIN = 3;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    const STATUS_MAP = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    private $service = null;

    protected $fillable = [
        'name',
        'email',
        'shopify_data',
        'shopify_id',
        'password',
        'role',
        'status',
        'shopify_token',
        'shopify_username',
    ];

    public static array $columns = [
        'id',
        'name',
        'email',
        'shopify_data',
        'status',
        'shopify_username',
    ];


    public function getDomain(): string
    {
        if (!str_contains($this->shopify_username, '.myshopify.com')) {
            return $this->shopify_username . '.myshopify.com';
        }

        return $this->shopify_username;
    }

    public function getService(): ShopifyService
    {
        if (!$this->service) {
            $this->service = new ShopifyService($this);
        }

        return $this->service;
    }
}
