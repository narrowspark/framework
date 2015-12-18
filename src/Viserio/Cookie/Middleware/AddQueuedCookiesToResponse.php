<?php
namespace Viserio\Cookie\Middleware;

use Closure;
use Viserio\Contracts\Cookie\QueueingFactory as CookieJar;
use Psr\Http\Message\RequestInterface as RequestContract;

class AddQueuedCookiesToResponse
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
     * Handle an incoming request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(RequestContract $request, Closure $next)
    {
        $response = $next($request);

        foreach ($this->cookies->getQueuedCookies() as $cookie) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}
