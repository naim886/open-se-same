<?php

namespace App\Providers;

use App\Support\AdminPermission;
use App\View\Composers\FrontendComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // The super role short-circuits every Gate check (and therefore every
        // @can / $user->can() in the backend) without touching the pivot tables.
        Gate::before(static function ($user, string $ability) {
            if (! method_exists($user, 'hasRole')) {
                return null;
            }

            return $user->hasRole(AdminPermission::superRole()) ? true : null;
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Share $footerPages to every frontend layout view
        View::composer('frontend.layouts.app', FrontendComposer::class);
    }
}
