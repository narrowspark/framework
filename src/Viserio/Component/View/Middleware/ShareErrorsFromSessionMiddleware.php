<?php
declare(strict_types=1);
namespace Viserio\Component\View\Middleware;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\View\Factory as FactoryContract;

class ShareErrorsFromSessionMiddleware implements ServerMiddlewareInterface
{
    /**
     * The view factory implementation.
     *
     * @var \Viserio\Component\Contracts\View\Factory
     */
    protected $view;

    /**
     * Create a new error binder instance.
     *
     * @param \Viserio\Component\Contracts\View\Factory $view
     */
    public function __construct(FactoryContract $view)
    {
        $this->view = $view;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        if (($session = $request->getAttribute('session')) !== null) {
            $this->view->share(
                'errors',
                $session->get('errors', [])
            );
        }

        return $delegate->process($request);
    }
}
