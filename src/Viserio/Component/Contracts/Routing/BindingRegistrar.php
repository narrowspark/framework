<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

use Closure;

interface BindingRegistrar
{
    /**
     * Add a new route parameter binder.
     *
     * @param  string  $key
     * @param  string|callable  $binder
     *
     * @return void
     */
    public function bind(string $key, $binder): void;

    /**
     * Get the binding callback for a given binding.
     *
     * @param string $key
     *
     * @return \Closure
     */
    public function getBindingCallback(string $key): Closure;
}
