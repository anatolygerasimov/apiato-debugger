<?php

namespace App\Containers\AppSection\Debugger\Middlewares;

use App\Ship\Parents\Middlewares\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

class XHProfMiddleware extends Middleware
{
    /**
     * @psalm-return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        xhprof_enable(config('xhprof.flags', 0));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $xhprofData = xhprof_disable();
        $route      = $request->route();

        if ($route instanceof Route) {
            $type = $route->getName();
            file_put_contents(
                $this->fileName($type),
                serialize($xhprofData)
            );
        }
    }

    private function fileName(?string $type = null, ?string $runId = null): string
    {
        $dir    = app('config')->get('xhprof.output_dir');
        $suffix = app('config')->get('xhprof.suffix');
        $type   ??= app('config')->get('xhprof.name');
        $runId  ??= uniqid();

        if (empty($dir) && !($dir = getenv('XHPROF_OUTPUT_DIR'))) {
            $dir = ini_get('xhprof.output_dir');

            if (empty($dir)) {
                $dir = sys_get_temp_dir();
            }
        }

        $file = sprintf('%s.%s.%s', $runId, $type, $suffix);

        if (!empty($dir)) {
            $file = sprintf('%s/%s', $dir, $file);
        }

        return $file;
    }
}
