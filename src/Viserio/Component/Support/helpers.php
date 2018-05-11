<?php
declare(strict_types=1);

use Viserio\Component\Support\Debug\Dumper;
use Viserio\Component\Support\Env;

if (! \function_exists('env')) {
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

if (! \function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param array<int, mixed> $args
     *
     * @return void
     */
    function dd(...$args): void
    {
        foreach ($args as $x) {
            Dumper::dump($x);
        }

        die(1);
    }
}

if (! \function_exists('retry')) {
    /**
     * Retry an operation a given number of times.
     *
     * @param int      $times
     * @param callable $callback
     * @param int      $sleep
     *
     * @throws \Throwable
     *
     * @return mixed
     */
    function retry(int $times, callable $callback, int $sleep = 0)
    {
        $times--;
        beginning:

        try {
            return $callback();
        } catch (\Throwable $e) {
            if ($times === 0) {
                throw $e;
            }

            $times--;

            if ($sleep !== 0) {
                \usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}
