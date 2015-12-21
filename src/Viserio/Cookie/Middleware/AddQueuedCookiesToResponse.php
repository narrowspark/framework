<?php
namespace Viserio\Cookie\Middleware;

use Psr\Http\Message\ResponseInterface as ResponseContract;
use Psr\Http\Message\ServerRequestInterface as RequestContract;
use Viserio\Contracts\Cookie\QueueingFactory as CookieJar;
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
     *
     * @return void
     */
    public function __construct(CookieJar $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(RequestContract $request, ResponseContract $response, callable $next)
    {
        $response = $next($request, $response, null);

        foreach ($this->cookies->getQueuedCookies() as $cookie) {
            $response->withHeader('Cookie', $cookie);
        }

        return $response;
    }
}
