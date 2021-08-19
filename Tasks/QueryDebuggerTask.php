<?php

namespace App\Containers\AppSection\Debugger\Tasks;

use App\Ship\Parents\Tasks\Task;
use DateTimeInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryDebuggerTask extends Task
{
    public function __construct(private DatabaseManager $db)
    {
    }

    /**
     * Write the DB queries in the Log and Display them in the
     * terminal (in case you want to see them while executing the tests).
     */
    public function run(): void
    {
        $debuggerEnabled = config('debugger.queries.debug');

        if ($debuggerEnabled) {
            $consoleOutputEnabled = config('debugger.queries.output.console');
            $logOutputEnabled     = config('debugger.queries.output.log');

            DB::listen(function (QueryExecuted $event) use ($consoleOutputEnabled, $logOutputEnabled) {
                $bindings = $event->bindings;
                // We need to transform all bindings to a readable value the same fashion
                // as the one used in \Illuminate\Database\Connection::prepareBindings(array $bindings)
                foreach ($bindings as $key => $value) {
                    if ($value instanceof DateTimeInterface) {
                        $bindings[$key] = $value->format($this->db->getQueryGrammar()->getDateFormat());
                    } elseif (is_bool($value)) {
                        $bindings[$key] = (int)$value;
                    }
                }
                $fullQuery = vsprintf(str_replace(['%', '?'], ['%%', '%s'], $event->sql), $bindings);

                $result = sprintf('%s (%s): %s', $event->connectionName, $event->time, $fullQuery);

                if ($consoleOutputEnabled) {
                    dump($result);
                }

                if ($logOutputEnabled) {
                    Log::info($result);
                }
            });
        }
    }
}