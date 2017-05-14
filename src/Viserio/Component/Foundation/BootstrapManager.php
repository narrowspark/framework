<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Closure;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;

final class BootstrapManager
{
    use ContainerAwareTrait;

    /**
     * Indicates if the application has been bootstrapped before.
     *
     * @var bool
     */
    private $hasBeenBootstrapped = false;

    /**
     * The array of bootstrapping callbacks.
     *
     * @var array
     */
    private $bootstrappingCallbacks = [];

    /**
     * The array of booted callbacks.
     *
     * @var array
     */
    private $bootstrappedCallbacks = [];

    /**
     * Create a new bootstrap manger instance.
     *
     * @param \Viserio\Component\Contracts\Container\Container $container
     */
    public function __construct(ContainerContract $container)
    {
        $this->container = $container;
    }

    /**
     * Register a callback to run before a bootstrapper.
     *
     * @param string   $bootstrapper
     * @param \Closure $callback
     *
     * @return void
     */
    public function addBeforeBootstrapping(string $bootstrapper, Closure $callback): void
    {
        $this->bootstrappingCallbacks['bootstrapping: ' . $bootstrapper][] = $callback;
    }

    /**
     * Register a callback to run after a bootstrapper.
     *
     * @param string   $bootstrapper
     * @param \Closure $callback
     *
     * @return void
     */
    public function addAfterBootstrapping(string $bootstrapper, Closure $callback): void
    {
        $this->bootstrappedCallbacks['bootstrapped: ' . $bootstrapper][] = $callback;
    }

    /**
     * Run the given array of bootstrap classes.
     *
     * @param array $bootstrappers
     *
     * @return void
     */
    public function bootstrapWith(array $bootstrappers): void
    {
        $kernel = $this->getContainer()->get(KernelContract::class);

        foreach ($bootstrappers as $bootstrap) {
            foreach ($this->bootstrappingCallbacks as $name => $callback) {
                if ('bootstrapping: ' . str_replace('\\', '', $bootstrap) === $name) {
                    $callback($kernel);
                }
            }

            $this->getContainer()->resolve($bootstrap)->bootstrap($kernel);

            foreach ($this->bootstrappedCallbacks as $name => $callback) {
                if ('bootstrapped: ' . str_replace('\\', '', $bootstrap) === $name) {
                    $callback($kernel);
                }
            }
        }

        $this->hasBeenBootstrapped = true;
    }

    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }
}
