<?php
declare(strict_types=1);
namespace Viserio\Component\View\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\View\Factory as FactoryContract;

class ShareErrorsFromSessionMiddleware implements MiddlewareInterface
{
    /**
     * The view factory implementation.
     *
     * @var \Viserio\Component\Contract\View\Factory
     */
    protected $view;

    /**
     * Create a new error binder instance.
     *
     * @param \Viserio\Component\Contract\View\Factory $view
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
