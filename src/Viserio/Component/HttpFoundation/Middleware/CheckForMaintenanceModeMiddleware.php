<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\HttpFoundation\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\HttpFoundation\Exception\MaintenanceModeException;
use Viserio\Contract\HttpFoundation\HttpKernel as HttpKernelContract;

class CheckForMaintenanceModeMiddleware implements MiddlewareInterface
{
    /**
     * The http kernel implementation.
     *
     * @var \Viserio\Contract\HttpFoundation\HttpKernel
     */
    protected $kernel;

    /**
     * Create a new maintenance check middleware instance.
     */
    public function __construct(HttpKernelContract $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\HttpFoundation\Exception\MaintenanceModeException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->kernel->isDownForMaintenance()) {
            $data = \json_decode(\file_get_contents($this->kernel->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'down')), true);

            throw new MaintenanceModeException((int) $data['time'], (int) $data['retry'], $data['message']);
        }

        return $handler->handle($request);
    }
}
