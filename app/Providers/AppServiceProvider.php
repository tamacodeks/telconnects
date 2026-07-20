<?php

namespace App\Providers;

use App\Support\LocalUrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();

            $app->instance('routes', $routes);

            $url = new LocalUrlGenerator(
                $routes,
                $app->rebinding('request', function ($app, $request) {
                    $app['url']->setRequest($request);
                }),
                function (Request $request) use ($app) {
                    $host = $request->getHost();

                    if (! $app->environment(['local', 'testing'])) {
                        return false;
                    }

                    return in_array($host, ['127.0.0.1', 'localhost', '::1'], true)
                        || Str::endsWith($host, ['.localhost', '.test']);
                }
            );

            $url->setSessionResolver(function () use ($app) {
                return $app['session'];
            });

            $url->setKeyResolver(function () use ($app) {
                return $app->make('config')->get('app.key');
            });

            $app->rebinding('routes', function ($app, $routes) {
                $app['url']->setRoutes($routes);
            });

            return $url;
        });

        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
