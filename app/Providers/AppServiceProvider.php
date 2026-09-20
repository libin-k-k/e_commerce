<?php

namespace App\Providers;

use App\Core\Commerce\MergeGuestCommerce;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, MergeGuestCommerce::class);

        // Use the host the browser hit (not a hardcoded APP_URL) for absolute URLs.
        if (! $this->app->runningInConsole()) {
            $this->app->booted(function (): void {
                $request = request();

                if ($request->getHost() !== '') {
                    URL::forceRootUrl($request->getSchemeAndHttpHost());
                }
            });
        }
    }
}
