<?php

namespace App\Providers;

use App\Models\MetaTag;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MetaTagServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $currentPath = request()->path();
            $currentPath = $currentPath === '/' ? 'home' : $currentPath;

            // Coba dapatkan meta tag berdasarkan path saat ini
            $metaTag = MetaTag::getByPage($currentPath);

            // Jika tidak ditemukan, coba gunakan meta tag default (home)
            if (!$metaTag) {
                $metaTag = MetaTag::getByPage('home');
            }

            $view->with('metaTag', $metaTag);
        });
    }
}
