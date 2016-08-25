<?php
declare(strict_types=1);
namespace Viserio\Routing\Middlewares;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Delegate as DelegateContract;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;

class NotAllowedMiddleware implements ServerMiddlewareContract
{
    /**
     * All not allowed http methods.
     *
     * @var array
     */
    protected $allowed;

    /**
     * Create a found middleware instance.
     *
     * @param array $allowed
     */
    public function __construct(array $allowed)
    {
        $this->allowed = $allowed;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        DelegateContract $frame
    ): ResponseInterface {
        throw new MethodNotAllowedException();
    }
}
