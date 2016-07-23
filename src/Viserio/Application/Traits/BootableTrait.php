<?php

declare(strict_types=1);
namespace Viserio\Application\Traits;

trait BootableTrait
{
    /**
     * Boots all providers.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The array of booting callbacks.
     *
     * @var array
     */
    protected $bootingCallbacks = [];

    /**
     * The array of booted callbacks.
     *
     * @var array
     */
    protected $bootedCallbacks = [];

    /**
     * Boots all service providers.
     *
     * This method is automatically called by finalize(), but you can use it
     * to boot all service providers when not handling a request.
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        if (! $this->booted) {
            $this->booted = true;

            foreach ($this->serviceProviders as $provider) {
                // If the application has already booted, we will call this boot method on
                // the provider class so it has an opportunity to do its boot logic and
                // will be ready for any usage by the developer's application logics.
                if (method_exists($provider, 'boot')) {
                    $provider->boot();
                }
            }
        }

        $this->resolveStack();
        $this->bootApplication();
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * Register a new boot listener.
     *
     * @param mixed $callback
     */
    public function booting($callback)
    {
        $this->bootingCallbacks[] = $callback;
    }

    /**
     * Register a new "booted" listener.
     *
     * @param mixed $callback
     */
    public function booted($callback)
    {
        $this->bootedCallbacks[] = $callback;

        if ($this->isBooted()) {
            $this->fireAppCallbacks([$callback]);
        }
    }

    /**
     * Resolve stack middlewares.
     *
     * @return \Stack\Builder
     */
    abstract public function resolveStack();

    /**
     * Boot the application and fire app callbacks.
     */
    protected function bootApplication()
    {
        // Once the application has booted we will also fire some "booted" callbacks
        // for any listeners that need to do work after this initial booting gets
        // finished. This is useful when ordering the boot-up processes we run.
        $this->fireAppCallbacks($this->bootingCallbacks);

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }

    /**
     * Call the booting callbacks for the application.
     *
     * @param array $callbacks
     */
    protected function fireAppCallbacks(array $callbacks)
    {
        foreach ($callbacks as $callback) {
            call_user_func($callback, $this);
        }
    }
}
