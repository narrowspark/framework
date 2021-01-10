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
