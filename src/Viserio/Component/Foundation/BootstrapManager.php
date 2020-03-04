<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Foundation;

use Viserio\Contract\Foundation\BootstrapManager as BootstrapManagerContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

final class BootstrapManager implements BootstrapManagerContract
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
     * @var \Viserio\Contract\Foundation\Kernel
     */
    private $kernel;

    /**
     * Create a new bootstrap manger instance.
     */
    public function __construct(KernelContract $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function addBeforeBootstrapping(string $bootstrapper, callable $callback): void
    {
        $key = 'bootstrapping: ' . \str_replace('\\', '', $bootstrapper);

        $this->bootstrappingCallbacks[$key][] = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function addAfterBootstrapping(string $bootstrapper, callable $callback): void
    {
        $key = 'bootstrapped: ' . \str_replace('\\', '', $bootstrapper);

        $this->bootstrappedCallbacks[$key][] = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrapWith(array $bootstraps): void
    {
        foreach ($bootstraps as $bootstrap) {
            if (! $bootstrap::isSupported($this->kernel)) {
                continue;
            }

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
     * Calls callbacks on bootstrap name.
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
