<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http\Middlewares;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Foundation\Http\Exceptions\MaintenanceModeException;

class CheckForMaintenanceModeMiddleware implements MiddlewareInterface
{
    /**
     * The http kernel implementation.
     *
     * @var \Viserio\Component\Contracts\Foundation\HttpKernel
     */
    protected $kernel;

    /**
     * Create a new maintenance check middleware instance.
     *
     * @param \Viserio\Component\Contracts\Foundation\HttpKernel $kernel
     */
    public function __construct(HttpKernelContract $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        if ($this->kernel->isDownForMaintenance()) {
            $data = json_decode(file_get_contents($this->kernel->getStoragePath('framework/down')), true);

            throw new MaintenanceModeException((int) $data['time'], (int) $data['retry'], $data['message']);
        }

        return $delegate->process($request);
    }
}
