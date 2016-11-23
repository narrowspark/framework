<?php
declare(strict_types=1);
namespace Viserio\Routing\Middlewares;

use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\RequestInterface;

class CallableMiddleware implements DelegateInterface
{
    /**
     * @var callable
     */
    private $middleware;

    /**
     * Create a new callable middleware instance.
     *
     * @param callable $middleware
     */
    public function __construct(callable $middleware)
    {
        $this->middleware = $middleware;
    }

    /**
     * {@inheritdoc}
     */
    public function process(RequestInterface $request)
    {
        return call_user_func($this->middleware, $request);
    }
}
