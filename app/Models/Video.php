<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Video
 * @package App\Models
 *
 * @property int $id
 * @property string $title
 * @property string $external_id
 * @property int $source
 * @property string $video_data
 * @property int $user_id
 */
class Video extends Model
{
    use HasFactory;

    CONST SOURCE_INSTAGRAM = 1;
    CONST SOURCE_TIKTOK = 2;
    CONST SOURCE_PC = 3;

    protected $fillable = [
        "title",
        "external_id",
        "source",
        "video_data",
        "user_id",
    ];
}
