<?php

namespace App\Containers\AppSection\Debugger\Providers;

use App\Containers\AppSection\Debugger\Middlewares\ProfilerMiddleware;
use App\Containers\AppSection\Debugger\Middlewares\QueryStatsHeaders;
use App\Containers\AppSection\Debugger\Middlewares\RequestsMonitorMiddleware;
use App\Containers\AppSection\Debugger\Middlewares\XHProfMiddleware;
use App\Ship\Parents\Providers\MiddlewareProvider;
use Illuminate\Contracts\Http\Kernel;

class MiddlewareServiceProvider extends MiddlewareProvider
{
    /**
     * Register Container Middleware Groups.
     */
    protected array $middlewareGroups = [
        'api' => [
            ProfilerMiddleware::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();

        if (config('app.debug')) {
            $this->app->make(Kernel::class)->appendMiddlewareToGroup('api', QueryStatsHeaders::class);
        }

        if (config('debugger.requests.debug')) {
            $this->app->make(Kernel::class)->prependMiddleware(RequestsMonitorMiddleware::class);
        }

        if (config('xhprof.requests.profiling')) {
            $this->app->make(Kernel::class)->prependMiddleware(XHProfMiddleware::class);
        }
    }
}
