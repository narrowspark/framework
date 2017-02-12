<?php
declare(strict_types=1);

use Viserio\Component\Support\Debug\Dumper;
use Viserio\Component\Support\Env;

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        return Env::get($key, $default);
    }
}

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param mixed
     *
     * @return void
     */
    function dd(): void
    {
        $caller = debug_backtrace()[0];
        $caller = $caller['function'] . ':' . $caller['line'];

        // if (config('app.debug')) {
        //     array_map(function ($x) {
        //         (new Dumper)->dump($x);
        //     }, array_merge(["Executed in $caller"], func_get_args()));
        // } else {
        //     logger()->warning("Attempted to use dd outside debug mode in $caller", func_get_args());
        // }

        die(1);
    }
}
