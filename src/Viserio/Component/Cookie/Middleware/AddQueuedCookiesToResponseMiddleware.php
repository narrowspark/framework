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
     *
     * @param \Viserio\Contract\Cookie\QueueingFactory $cookies
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
