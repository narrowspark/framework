<?php
namespace Viserio\Exception\Providers;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\HttpFoundation\Request;
use Viserio\Application\ServiceProvider;
use Viserio\Exception\Adapter\ArrayDisplayer;
use Viserio\Exception\Adapter\PlainDisplayer;
use Viserio\Exception\Adapter\SymfonyDisplayer;
use Viserio\Exception\Adapter\WhoopsDisplayer;
use Viserio\Exception\Handler as ExceptionHandler;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * ExceptionServiceProvider.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2
 */
class ExceptionServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerDisplayers();

        $this->app->singleton('exception', function ($app) {
            // stop PHP from polluting exception messages
            // with html that Whoops escapes and prints.
            ini_set('html_errors', false);

            return new ExceptionHandler(
                $app,
                $app->get('logger')->getMonolog(),
                $this->app->get('config')->get('app::debug')
            );
        });
    }

    /**
     * Register the exception displayers.
     */
    protected function registerDisplayers()
    {
        switch ($this->app->get('config')->get('app::exception.handler')) {
            case 'whoops':
                $this->registerWhoopsDebugDisplayer();
                break;
            case 'symfony':
                $this->registerSymfonyDebugDisplayer();
                break;
            case 'array':
                $this->registerArrayDebugDisplayer();
                break;
            default:
                $this->registerArrayDebugDisplayer();
                break;
        }

        $this->registerPlainDisplayer();
    }

    /**
     * Register the plain exception displayer.
     */
    protected function registerPlainDisplayer()
    {
        $this->app->bind('exception.plain', function ($app) {
            // If the application is running in a console environment, we will just always
            // use the debug handler as there is no point in the console ever returning
            // out HTML. This debug handler always returns JSON from the console env.
            if ($app->get('environment')->runningInConsole()) {
                return $app->get('exception.debug');
            }

            return new PlainDisplayer();
        });
    }

    /**
     * Register the Whoops exception displayer.
     */
    protected function registerWhoopsDebugDisplayer()
    {
        $this->registerWhoops();

        $this->app->bind('exception.debug', function ($app) {
            return new WhoopsDisplayer(
                $app->get('whoops'),
                $app->get('environment')->runningInConsole()
            );
        });
    }

    /**
     * Register the Symfony exception displayer.
     */
    protected function registerSymfonyDebugDisplayer()
    {
        $this->app->bind('exception.debug', function () {
            return new SymfonyDisplayer(
                new SymfonyExceptionHandler(),
                $this->shouldReturnJson()
            );
        });
    }

    /**
     * Register the array exception displayer.
     */
    protected function registerArrayDebugDisplayer()
    {
        $this->app->bind('exception.debug', function () {
            return new ArrayDisplayer();
        });
    }

    /**
     * Register the Whoops error display service.
     */
    protected function registerWhoops()
    {
        $this->registerWhoopsHandler();

        $request = $this->app->get('request');

        if ($request === null) {
            // This error occurred too early in the application's life
            // and the request instance is not yet available.
            return;
        }

        $this->registerPrettyWhoopsHandlerInfo($request);

        $this->app->singleton('whoops', function ($app) {
            // We will instruct Whoops to not exit after it displays the exception as it
            // will otherwise run out before we can do anything else. We just want to
            // let the framework go ahead and finish a request on this end instead.
            $whoops = new Run();
            $whoops->allowQuit(false);

            $whoops->writeToOutput(true);
            $whoops->pushHandler($app['whoops.handler']);

            if (!$this->shouldReturnJson()) {
                $whoops->pushHandler($app->get('whoops.plain.handler'));
                $whoops->pushHandler($app->get('whoops.handler.info'));
            }

            return $whoops;
        });
    }

    /**
     * Register the Whoops handler for the request.
     */
    protected function registerWhoopsHandler()
    {
        if ($this->shouldReturnJson()) {
            $this->app->bind('whoops.handler', function () {
                $handler = new JsonResponseHandler();

                $handler->onlyForAjaxRequests(true);
                $handler->addTraceToOutput(true);

                return $handler;
            });
        } else {
            $this->registerPlainTextHandler();

            $this->registerPrettyWhoopsHandler();
        }
    }

    /**
     * Register the Whoops handler for the request.
     */
    protected function registerPlainTextHandler()
    {
        $this->app->bind('whoops.plain.handler', function ($app) {
            $handler = new PlainTextHandler($app->get('logger')->getMonolog());

            $handler->onlyForCommandLine(false);
            $handler->outputOnlyIfCommandLine(false);
            $handler->loggerOnly(true);

            $handler->setLogger($app->get('logger')->getMonolog());

            return $handler;
        });
    }

    /**
     * Determine if the error provider should return JSON.
     *
     * @return bool
     */
    protected function shouldReturnJson()
    {
        return $this->app->get('environment')->runningInConsole() || $this->requestWantsJson();
    }

    /**
     * Determine if the request warrants a JSON response.
     *
     * @return bool
     */
    protected function requestWantsJson()
    {
        return $this->app->get('request')->ajax() || $this->app->get('request')->wantsJson();
    }

    /**
     * Register the "pretty" Whoops handler.
     */
    protected function registerPrettyWhoopsHandler()
    {
        $this->app->bind('whoops.handler', function ($app) {
            $handler = new PrettyPageHandler();
            $handler->setEditor($app['config']->get('app::whoops.editor', 'sublime'));

            if ($this->resourcePath() !== null) {
                $handler->addResourcePath($this->resourcePath());
            }

            return $handler;
        });
    }

    /**
     * Get the resource path for Whoops.
     *
     * @return string|null
     */
    public function resourcePath()
    {
        if (is_dir($path = $this->getResourcePath())) {
            return $path;
        }
    }

    /**
     * Get the Whoops custom resource path.
     *
     * @return string
     */
    protected function getResourcePath()
    {
        $base = $this->app->basePath();

        return $base.'/vendor/narrowspark/framework/src/Viserio/Exception/Resources';
    }

    /**
     * Retrieves info on the Narrowspark environment and ships it off
     * to the PrettyPageHandler's data tables:.
     *
     * This works by adding a new handler to the stack that runs
     * before the error page, retrieving the shared page handler
     * instance, and working with it to add new data tables
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    protected function registerPrettyWhoopsHandlerInfo(Request $request)
    {
        $this->app->bind('whoops.handler.info', function ($app) use ($request) {
            $whoops = $app->get('whoops.handler');

            $whoops->setPageTitle("We're all going to be fired!");

            $whoops->addDataTable('Narrowspark Application', [
                'Version' => $app->getVersion(),
                'Charset' => $app->get('config')->get('app::locale'),
                'Route Class' => get_class($app->get('route')),
                'Application Class' => get_class($app),
            ]);

            $whoops->addDataTable('Narrowspark Application (Request)', [
                'URI' => $request->getUri(),
                'Request URI' => $request->getRequestUri(),
                'Path Info' => $request->getPathInfo(),
                'Query String' => $request->getQueryString() ?: '<none>',
                'HTTP Method' => $request->getMethod(),
                'Script Name' => $request->getScriptName(),
                'Base Path' => $request->getBasePath(),
                'Base URL' => $request->getBaseUrl(),
                'Scheme' => $request->getScheme(),
                'Port' => $request->getPort(),
                'Host' => $request->getHost(),
            ]);

            return $whoops;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'exception.debug',
            'exception.plain',
            'exception',
            'whoops',
            'whoops.handler',
            'whoops.handler.info',
            'whoops.plain.handler',
        ];
    }
}
