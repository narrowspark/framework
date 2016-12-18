<?php
declare(strict_types=1);
namespace Viserio\View\Middleware;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\View\Factory as FactoryContract;

class ShareErrorsFromSessionMiddleware implements ServerMiddlewareInterface
{
    /**
     * The view factory implementation.
     *
     * @var \Viserio\Contracts\View\Factory
     */
    protected $view;

    /**
     * Create a new error binder instance.
     *
     * @param \Viserio\Contracts\View\Factory $view
     */
    public function __construct(FactoryContract $view)
    {
        $this->view = $view;
    }

    /**
     * {@inhertidoc}.
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $this->view->share(
            'errors',
            $request->getAttribute('session')->get('errors', [])
        );

        return $delegate->process($request);
    }
}
