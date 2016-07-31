<?php
declare(strict_types=1);
namespace Viserio\Cookie\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Cookie\QueueingFactory as CookieJar;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;

class AddQueuedCookiesToResponse implements MiddlewareContract
{
    /**
     * The cookie jar instance.
     *
     * @var \Viserio\Contracts\Cookie\QueueingFactory
     */
    protected $cookies;

    /**
     * Create a new CookieQueue instance.
     *
     * @param \Viserio\Contracts\Cookie\QueueingFactory $cookies
     */
    public function __construct(CookieJar $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        ServerRequestInterface $request,
        FrameContract $frame
    ): ResponseInterface {
        $response = $frame->next($request);

        foreach ($this->cookies->getQueuedCookies() as $cookie) {
            $response->withHeader('Cookie', $cookie);
        }

        return $response;
    }
}
