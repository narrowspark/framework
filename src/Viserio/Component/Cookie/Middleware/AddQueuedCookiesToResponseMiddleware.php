<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\Contract\Cookie\QueueingFactory as CookieJar;
use Viserio\Component\Cookie\ResponseCookies;

class AddQueuedCookiesToResponseMiddleware implements MiddlewareInterface
{
    /**
     * The cookie jar instance.
     *
     * @var \Viserio\Component\Contract\Cookie\QueueingFactory
     */
    protected $cookies;

    /**
     * Create a new CookieQueue instance.
     *
     * @param \Viserio\Component\Contract\Cookie\QueueingFactory $cookies
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
        $cookies  = ResponseCookies::fromResponse($response);

        foreach ($this->cookies->getQueuedCookies() as $name => $cookie) {
            $cookies = $cookies->add($cookie);
        }

        return $cookies->renderIntoSetCookieHeader($response);
    }
}
