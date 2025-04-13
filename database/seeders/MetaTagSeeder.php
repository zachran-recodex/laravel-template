<?php

namespace Database\Seeders;

use App\Models\MetaTag;
use Illuminate\Database\Seeder;

class MetaTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat meta tag default untuk halaman utama
        MetaTag::create([
            'page' => 'home',
            'title' => 'Laravel Template - Home',
            'description' => 'Selamat datang di Laravel Template, aplikasi web modern dengan fitur lengkap',
            'keywords' => 'laravel, template, web, aplikasi, modern',
            'author' => 'Laravel Template Team',
            'og_title' => 'Laravel Template - Home',
            'og_description' => 'Selamat datang di Laravel Template, aplikasi web modern dengan fitur lengkap',
            'og_image' => '/images/og-image.jpg',
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'twitter_title' => 'Laravel Template - Home',
            'twitter_description' => 'Selamat datang di Laravel Template, aplikasi web modern dengan fitur lengkap',
            'twitter_image' => '/images/twitter-image.jpg',
        ]);
    }
}
