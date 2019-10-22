<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\View\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Contract\View\Factory as FactoryContract;

class ShareErrorsFromSessionMiddleware implements MiddlewareInterface
{
    /**
     * The view factory implementation.
     *
     * @var \Viserio\Contract\View\Factory
     */
    protected $view;

    /**
     * Create a new error binder instance.
     *
     * @param \Viserio\Contract\View\Factory $view
     */
    public function __construct(FactoryContract $view)
    {
        $this->view = $view;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (($session = $request->getAttribute('session')) !== null) {
            $this->view->share(
                'errors',
                $session->get('errors', [])
            );
        }

        return $handler->handle($request);
    }
}
