<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Http\Middlewares;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

class CheckForMaintenanceModeMiddleware implements MiddlewareInterface
{
    /**
     * The config implementation.
     *
     * @var \Viserio\Component\Contracts\Config\Repository
     */
    protected $config;

    /**
     * Create a new maintenance check middleware instance.
     *
     * @param \Viserio\Component\Contracts\Config\Repository $config
     */
    public function __construct(RepositoryContract $config)
    {
        $this->config = $config;
    }

    /**
     * {@inhertidoc}.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        if ($this->config->get('app.maintenance', false)) {
            $data = json_decode(file_get_contents($this->config->get('path.storage') . '/framework/down'), true);

            throw new MaintenanceModeException($data['time'], $data['retry'], $data['message']);
        }

        return $delegate->process($request);
    }
}
