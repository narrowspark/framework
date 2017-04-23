<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Profiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Support\Env;
use Viserio\Component\Profiler\DataCollectors\PhpInfoDataCollector;

class NarrowsparkDataCollector extends PhpInfoDataCollector implements TooltipAwareContract
{
    /**
     * A server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $serverRequest;

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        parent::collect($serverRequest, $response);

        $this->serverRequest = $serverRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'narrowspark';
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuPosition(): string
    {
        return 'right';
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => 'ic_narrowspark_white_24px.svg',
            'label' => '',
            'value' => AbstractKernel::VERSION,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        $debug   = Env::get('APP_DEBUG', false);
        $opcache = extension_loaded('Zend OPcache') && ini_get('opcache.enable');

        $tooltip = $this->createTooltipGroup([
            'Profiler token'   => $this->serverRequest->getHeaderLine('X-Debug-Token'),
            'Application name' => '',
            'Environment'      => Env::get('APP_ENV', 'develop'),
            'Debug'            => [
                [
                    'class' => $debug !== false ? 'status-green' : 'status-red',
                    'value' => $debug !== false ? 'enabled' : 'disabled',
                ],
            ],
        ]);

        $tooltip .= $this->createTooltipGroup([
            'PHP version'    => phpversion(),
            'Architecture'   => PHP_INT_SIZE * 8,
            'Timezone'       => date_default_timezone_get(),
            'PHP Extensions' => [
                [
                    'class' => extension_loaded('xdebug') ? 'status-green' : 'status-red',
                    'value' => 'Xdebug',
                ],
                [
                    'class' => $opcache ? 'status-green' : 'status-red',
                    'value' => 'OPcache',
                ],
            ],
            'PHP SAPI' => php_sapi_name(),
        ]);

        $version = AbstractKernel::VERSION;

        $tooltip .= $this->createTooltipGroup([
            'Resources' => '<a href="//narrowspark.de/doc/' . $version . '">Read Narrowspark Doc\'s ' . $version . '</a>',
            'Help'      => '<a href="//narrowspark.de/support">Narrowspark Support Channels</a>',
        ]);

        return $tooltip;
    }
}
