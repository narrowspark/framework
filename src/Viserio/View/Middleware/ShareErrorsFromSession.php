<?php
declare(strict_types=1);
namespace Viserio\View\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;
use Viserio\Contracts\View\Factory as ViewFactory;

class ShareErrorsFromSession implements MiddlewareContract
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
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }

    public function handle(
        ServerRequestInterface $request,
        FrameContract $frame
    ): ResponseInterface {
        // If the current session has an "errors" variable bound to it, we will share
        // its value with all view instances so the views can easily access errors
        // without having to bind. An empty bag is set when there aren't errors.
        $this->view->share(
            'errors', /*TODO HTTP*/ ''
        );

        // Putting the errors in the view for every view allows the developer to just
        // assume that some errors are always available, which is convenient since
        // they don't have to continually run checks for the presence of errors.
        return $frame->next($request);
    }
}
