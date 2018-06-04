<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Profiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Profiler\DataCollector\PhpInfoDataCollector;

class NarrowsparkDataCollector extends PhpInfoDataCollector implements TooltipAwareContract
{
    /**
     * A server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $serverRequest;

    /**
     * Gets the environment.
     *
     * @var string
     */
    private $env;

    /**
     * Is debug enabled.
     *
     * @var bool
     */
    private $isDebug;

    /**
     * Create a new narrowspark collector instance.
     *
     * @param string $env
     * @param bool   $isDebug
     */
    public function __construct($env = 'local', $isDebug = true)
    {
        $this->env     = $env;
        $this->isDebug = $isDebug;
    }

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
        $opcache = \extension_loaded('Zend OPcache') && \ini_get('opcache.enable');

        $tooltip = $this->createTooltipGroup([
            'Profiler token'   => $this->serverRequest->getHeaderLine('x-debug-token'),
            'Application name' => '',
            'Environment'      => $this->env,
            'Debug'            => [
                [
                    'class' => $this->isDebug !== false ? 'status-green' : 'status-red',
                    'value' => $this->isDebug !== false ? 'enabled' : 'disabled',
                ],
            ],
        ]);

        $tooltip .= $this->createTooltipGroup([
            'PHP version'    => \PHP_VERSION,
            'Architecture'   => \PHP_INT_SIZE * 8,
            'Timezone'       => \date_default_timezone_get(),
            'PHP Extensions' => [
                [
                    'class' => \extension_loaded('xdebug') ? 'status-green' : 'status-red',
                    'value' => 'Xdebug',
                ],
                [
                    'class' => $opcache ? 'status-green' : 'status-red',
                    'value' => 'OPcache',
                ],
            ],
            'PHP SAPI' => \PHP_SAPI,
        ]);

        $version = AbstractKernel::VERSION;

        $tooltip .= $this->createTooltipGroup([
            'Resource'  => '<a href="//narrowspark.de/doc/' . $version . '">Read Narrowspark Doc\'s ' . $version . '</a>',
            'Help'      => '<a href="//narrowspark.de/support">Narrowspark Support Channels</a>',
        ]);

        return $tooltip;
    }
}
