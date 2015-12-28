<?php
namespace Viserio\Application;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\RequestStack as SymfonyStackRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\TerminableInterface;

class HttpKernel implements HttpKernelInterface, TerminableInterface
{
    /**
     * Application instance.
     *
     * @var \Viserio\Application\Application
     */
    protected $app;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requests;

    /**
     * @param \Viserio\Application\Application               $app
     * @param \Symfony\Component\HttpFoundation\RequestStack $requests
     */
    public function __construct(Application $app, SymfonyStackRequest $requests)
    {
        $this->app = $app;
        $this->requests = $requests;
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * @param SymfonyRequest $request A Request instance
     * @param int            $type    The type of the request
     *                                (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool           $catch   Whether to catch exceptions or not
     *
     * @throws \Exception
     *
     * @return SymfonyResponse A Response instance
     */
    public function handle(SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        try {
            return $this->innerHandle($request, $type);
        } catch (\Exception $exception) {
            if (!$catch || $this->app->runningUnitTests()) {
                $this->finishRequest($request, $type);
                throw $exception;
            }

            return $this->handleException($exception, $request, $type);
        }
    }

    /**
     * Handle the request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int                                       $type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function innerHandle(SymfonyRequest $request, $type)
    {
        $this->requests->push($request);
        $dispatcher = $this->app['route']->getDispatcher();

        $event = new GetResponseEvent($this, $request, $type);
        $this->app['events']->dispatch(KernelEvents::REQUEST, $event);
        $response = $event->getResponse() ?: $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

        return $this->filterResponse($response, $request, $type);
    }

    /**
     * [filterResponse description].
     *
     * @param \Symfony\Component\HttpFoundation\Request  $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param int                                        $type
     *
     * @return SymfonyResponse
     */
    protected function filterResponse(SymfonyResponse $response, SymfonyRequest $request, $type)
    {
        $event = new FilterResponseEvent($this, $request, $type, $response);
        $this->app['events']->dispatch(KernelEvents::RESPONSE, $event);

        $response = $event->getResponse();
        $response->prepare($request);

        $this->finishRequest($request, $type);

        return $response;
    }

    /**
     * Handle the request exception.
     *
     * @param \Exception                                $exception
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int                                       $type
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleException(\Exception $exception, SymfonyRequest $request, $type)
    {
        $event = new GetResponseForExceptionEvent($this, $request, $type, $exception);
        $this->app['events']->dispatch(KernelEvents::EXCEPTION, $event);
        $response = $event->getResponse() ?: $this->app['exception']->handleException($exception);

        try {
            return $this->filterResponse($response, $request, $type);
        } catch (\Exception $exception) {
            return $response;
        }
    }

    /**
     * [finishRequest description].
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int                                       $type
     */
    protected function finishRequest(SymfonyRequest $request, $type)
    {
        $event = new FinishRequestEvent($this, $request, $type);
        $this->app['events']->dispatch(KernelEvents::FINISH_REQUEST, $event);

        $this->requests->pop();
    }

    /**
     * Terminates a request/response cycle.
     *
     * @param \Symfony\Component\HttpFoundation\Request  $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function terminate(SymfonyRequest $request, SymfonyResponse $response)
    {
        $event = new PostResponseEvent($this, $request, $response);
        $this->app['events']->dispatch(KernelEvents::TERMINATE, $event);
    }
}
