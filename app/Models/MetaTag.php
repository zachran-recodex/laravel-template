<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaTag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page',
        'title',
        'description',
        'keywords',
        'author',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
    ];

    /**
     * Get meta tags for a specific page
     *
     * @param string $page
     * @return MetaTag|null
     */
    public static function getByPage(string $page)
    {
        return self::where('page', $page)->first();
    }
}
