<?php
namespace Viserio\View\Providers;

use Viserio\Application\ServiceProvider;
use Viserio\View\Engines\Adapter\Php as PhpEngine;
use Viserio\View\Engines\EngineResolver;
use Viserio\View\Factory;
use Viserio\View\ViewFinder;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerEngineResolver();
        $this->registerViewFinder();
        $this->registerFactory();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            'view',
            'view.finder',
            'view.engine.resolver',
        ];
    }

    /**
     * Register the engine engines instance.
     */
    protected function registerEngineResolver()
    {
        $this->app->bind('view.engine.resolver', function ($app) {
            $resolver = new EngineResolver();

            // Next we will register the various engines with the engines so that the
            // environment can resolve the engines it needs for various views based
            // on the extension of view files. We call a method for each engines.
            foreach (['php' => 'php', 'phtml' => 'php'] as $engineName => $engineClass) {
                $this->{'register' . ucfirst($engineClass) . 'Engine'}($resolver);
            }

            if (($compilers = $app->get('config')->get('view::compilers')) !== null) {
                foreach ($compilers as $compilerName => $compilerClass) {
                    if ($compilerName === $compilerClass[0]) {
                        $this->registercustomEngine(
                            $compilerName,
                            call_user_func_array($compilerClass[0], (array) $compilerClass[1]),
                            $resolver
                        );
                    }
                }
            }

            return $resolver;
        });
    }

    /**
     * Register custom engine implementation.
     *
     * @param string                               $engineName
     * @param string                               $engineClass
     * @param \Viserio\View\Engines\EngineResolver $engines
     */
    protected function registercustomEngine(string $engineName, string $engineClass, \Viserio\View\Engines\EngineResolver $engines)
    {
        $engines->register($engineName, function () use ($engineClass) {
            return $engineClass;
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param \Viserio\View\Engines\EngineResolver $engines
     */
    protected function registerPhpEngine(\Viserio\View\Engines\EngineResolver $engines)
    {
        $engines->register('php', function () {
            return new PhpEngine();
        });
    }

    /**
     * Alias for PhpEngine.
     *
     * @method registerPhpEngine
     *
     * @param $engines
     */
    protected function registerPhtmlEngine($engines)
    {
        $this->registerPhpEngine($engines);
    }

    /**
     * Register the view finder implementation.
     */
    protected function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            return new ViewFinder($app->get('files'), $app->get('config')->get('view::template.paths'));
        });
    }

    /**
     * Register the view environment.
     */
    protected function registerFactory()
    {
        $this->app->singleton('view', function ($app) {
            $view = new Factory(
                $app->get('view.engine.resolver'),
                $app->get('view.finder'),
                $app->get('events')
            );

            $view->share('app', $app);

            return $view;
        });
    }
}
