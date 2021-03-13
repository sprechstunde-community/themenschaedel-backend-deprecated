<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/user/profile';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {

            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';

            Route::as('account.')
                ->domain('account.' . $domain)
                ->middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            Route::as('api.')
                ->domain('api.' . $domain)
                ->middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            // disable frontend routes while generating api auth routes
            $fortifyViews = config('fortify.views');
            config()->set('fortify.views', false);

            // generate api auth routes
            Route::as('api.auth.')
                ->domain('api.' . $domain)
                ->namespace('Laravel\Fortify\Http\Controllers')
                ->prefix('auth')
                ->group(base_path('vendor/laravel/fortify/routes/routes.php'));

            // restore original config
            config()->set('fortify.views', $fortifyViews);

        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
