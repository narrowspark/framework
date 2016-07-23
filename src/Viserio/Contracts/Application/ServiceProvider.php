<?php

declare(strict_types=1);
namespace Viserio\Contracts\Application;

interface ServiceProvider
{
    /**
     * Dynamically handle missing method calls.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, array $parameters);

    /**
     * Use the register method to register items with the container via the
     * protected $this->app property.
     */
    public function register();

    /**
     * Subscribe events.
     *
     * @param array|null $commands
     */
    public function commands(array $commands = null);

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array;
}
