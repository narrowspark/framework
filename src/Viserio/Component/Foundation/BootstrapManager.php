<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use Viserio\Component\Contract\Foundation\Kernel as KernelContract;

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
     * A Kernel implementation.
     *
     * @var \Viserio\Component\Contract\Foundation\Kernel
     */
    private $kernel;

    /**
     * Create a new bootstrap manger instance.
     *
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     */
    public function __construct(KernelContract $kernel)
    {
        $this->kernel = $kernel;
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
        $key = 'bootstrapping: ' . \str_replace('\\', '', $bootstrapper);

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
        $key = 'bootstrapped: ' . \str_replace('\\', '', $bootstrapper);

        $this->bootstrappedCallbacks[$key][] = $callback;
    }

    /**
     * Run the given array of bootstrap classes.
     *
     * @param array $bootstraps
     *
     * @return void
     */
    public function bootstrapWith(array $bootstraps): void
    {
        foreach ($bootstraps as $bootstrap) {
            $this->callCallbacks(
                $this->bootstrappingCallbacks,
                $this->kernel,
                'bootstrapping: ',
                $bootstrap
            );

            $bootstrap::bootstrap($this->kernel);

            $this->callCallbacks(
                $this->bootstrappedCallbacks,
                $this->kernel,
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
     * @param array                                         $bootCallbacks
     * @param \Viserio\Component\Contract\Foundation\Kernel $kernel
     * @param string                                        $type
     * @param string                                        $bootstrap
     *
     * @return void
     */
    private function callCallbacks(array $bootCallbacks, KernelContract $kernel, string $type, string $bootstrap): void
    {
        foreach ($bootCallbacks as $name => $callbacks) {
            if ($type . \str_replace('\\', '', $bootstrap) === $name) {
                /** @var callable $callback */
                foreach ($callbacks as $callback) {
                    $callback($kernel);
                }
            }
        }
    }
}
