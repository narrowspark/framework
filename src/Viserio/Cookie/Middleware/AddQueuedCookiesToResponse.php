<?php
declare(strict_types=1);
namespace Viserio\Cookie\Middleware;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Viserio\Contracts\Cookie\QueueingFactory as CookieJar;

class AddQueuedCookiesToResponse implements MiddlewareInterface
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
    public function process(RequestInterface $request, DelegateInterface $delegate)
    {
        $response = $delegate->process($request);

        foreach ($this->cookies->getQueuedCookies() as $cookie) {
            $response->withHeader('Cookie', $cookie);
        }

        return $response;
    }
}
