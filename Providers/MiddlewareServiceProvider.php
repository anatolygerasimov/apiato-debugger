<?php

namespace App\Containers\Vendor\Debugger\Providers;

use App\Containers\Vendor\Debugger\Middlewares\ProfilerMiddleware;
use App\Containers\Vendor\Debugger\Middlewares\QueryStatsHeaders;
use App\Containers\Vendor\Debugger\Middlewares\RequestsMonitorMiddleware;
use App\Containers\Vendor\Debugger\Middlewares\XHProfMiddleware;
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
