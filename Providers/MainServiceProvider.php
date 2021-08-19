<?php

namespace App\Containers\Vendor\Debugger\Providers;

use App\Containers\Vendor\Debugger\Tasks\QueryDebuggerTask;
use App\Ship\Parents\Models\Model;
use App\Ship\Parents\Providers\MainProvider;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\AgentServiceProvider;
use Jenssegers\Agent\Facades\Agent;

class MainServiceProvider extends MainProvider
{
    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Container Service Providers.
     */
    public array $serviceProviders = [
        AgentServiceProvider::class,
        MiddlewareServiceProvider::class,
    ];

    /**
     * Container Aliases.
     */
    public array $aliases = [
        'Agent' => Agent::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        if (config('app.debug')) {
            $this->app->make(DatabaseManager::class)->enableQueryLog();
        }

        // Manual listen db queries
        if (in_array(config('app.env'), config('debugbar.env_db_listen'), true)) {
            DB::listen(function (QueryExecuted $query) {
                info('TEST_CALL_API', [
                    $query->sql,
                    $query->bindings,
                    $query->time,
                ]);
            });
        }

        Model::preventLazyLoading(!$this->app->isProduction());

        if (!$this->app->isLocal()) {
            Model::handleLazyLoadingViolationUsing(function (EloquentModel $model, string $relation) {
                info(sprintf('Attempted to lazy load [%s] on model [%s].', $relation, $model::class));
            });
        }
    }

    /**
     * Register anything in the container.
     */
    public function register(): void
    {
        parent::register();

        if (
            $this->app->isLocal() &&
            class_exists(\Laravel\Telescope\TelescopeServiceProvider::class) &&
            config('telescope.enabled')
        ) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        app(QueryDebuggerTask::class)->run();
    }
}
