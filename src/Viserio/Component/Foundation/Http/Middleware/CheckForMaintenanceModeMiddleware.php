<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\Contract\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Foundation\Http\Exception\MaintenanceModeException;

class CheckForMaintenanceModeMiddleware implements MiddlewareInterface
{
    /**
     * The http kernel implementation.
     *
     * @var \Viserio\Component\Contract\Foundation\HttpKernel
     */
    protected $kernel;

    /**
     * Create a new maintenance check middleware instance.
     *
     * @param \Viserio\Component\Contract\Foundation\HttpKernel $kernel
     */
    public function __construct(HttpKernelContract $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Foundation\Http\Exception\MaintenanceModeException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->kernel->isDownForMaintenance()) {
            $data = \json_decode(\file_get_contents($this->kernel->getStoragePath('framework/down')), true);

            throw new MaintenanceModeException((int) $data['time'], (int) $data['retry'], $data['message']);
        }

        return $handler->handle($request);
    }
}
