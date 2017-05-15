<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;

final class BootstrapManager
{
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
     * A container instance.
     *
     * @var \Viserio\Component\Contracts\Container\Container
     */
    private $container;

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
     * @param callable $callback
     *
     * @return void
     */
    public function addBeforeBootstrapping(string $bootstrapper, callable $callback): void
    {
        $key = 'bootstrapping: ' . str_replace('\\', '', $bootstrapper);

        $this->bootstrappingCallbacks[$key][] = $callback;
    }

    /**
     * Register a callback to run after a bootstrapper.
     *
     * @param string   $bootstrapper
     * @param callable $callback
     *
     * @return void
     */
    public function addAfterBootstrapping(string $bootstrapper, callable $callback): void
    {
        $key = 'bootstrapped: ' . str_replace('\\', '', $bootstrapper);

        $this->bootstrappedCallbacks[$key][] = $callback;
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
        $kernel = $this->container->get(KernelContract::class);

        foreach ($bootstrappers as $bootstrap) {
            $this->callCallbacks(
                $this->bootstrappingCallbacks,
                $kernel,
                'bootstrapping: ',
                $bootstrap
            );

            $this->container->resolve($bootstrap)->bootstrap($kernel);

            $this->callCallbacks(
                $this->bootstrappedCallbacks,
                $kernel,
                'bootstrapped: ',
                $bootstrap
            );
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

    /**
     * Calls callbacks on bootstrap name.
     *
     * @param array                                          $bootCallbacks
     * @param \Viserio\Component\Contracts\Foundation\Kernel $kernel
     * @param string                                         $type
     * @param string                                         $bootstrap
     *
     * @return void
     */
    private function callCallbacks(
        array $bootCallbacks,
        KernelContract $kernel,
        string $type,
        string $bootstrap
    ): void {
        foreach ($this->bootstrappedCallbacks as $name => $callbacks) {
            if ($type . str_replace('\\', '', $bootstrap) === $name) {
                foreach ($callbacks as $callback) {
                    $callback($kernel);
                }
            }
        }
    }
}
