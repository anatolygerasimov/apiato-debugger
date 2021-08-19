<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Xhprof Settings
    |--------------------------------------------------------------------------
    */

    'requests' => [

        /*
        |--------------------------------------------------------------------------
        | Requests Debugger
        |--------------------------------------------------------------------------
        |
        | Enable/Disable requests debugger.
        |
        */

        'profiling' => env('REQUESTS_XHPROF', false),
    ],

    'flags' => 2 | 4, // XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY,

    'run_id' => 'xhprof_run_id',

    'name' => 'app',

    'suffix' => 'xhprof',
];
