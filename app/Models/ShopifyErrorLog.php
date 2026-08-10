<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ShopifyErrorLog
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property int $method
 * @property int $data
 * @property User $user
 */
class ShopifyErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method',
        'data',
    ];

    public static array $columns = [
        'id',
        'user_id',
        'method',
        'data',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
