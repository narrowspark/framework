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

namespace Viserio\Component\Cookie\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Contract\Cookie\QueueingFactory as CookieJar;

class AddQueuedCookiesToResponseMiddleware implements MiddlewareInterface
{
    /**
     * The cookie jar instance.
     *
     * @var \Viserio\Contract\Cookie\QueueingFactory
     */
    protected $cookies;

    /**
     * Create a new CookieQueue instance.
     */
    public function __construct(CookieJar $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $cookies = ResponseCookies::fromResponse($response);

        foreach ($this->cookies->getQueuedCookies() as $name => $cookie) {
            $cookies = $cookies->add($cookie);
        }

        return $cookies->renderIntoSetCookieHeader($response);
    }
}
