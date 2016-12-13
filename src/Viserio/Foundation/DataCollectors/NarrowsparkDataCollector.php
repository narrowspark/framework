<?php
declare(strict_types=1);
namespace Viserio\Foundation\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Foundation\Application;
use Viserio\Support\Env;
use Viserio\WebProfiler\DataCollectors\AbstractDataCollector;

class NarrowsparkDataCollector extends AbstractDataCollector implements TooltipAwareContract, MenuAwareContract
{
    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        //
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
            'icon' => file_get_contents(__DIR__ . '/Resources/icons/ic_narrowspark_white_24px.svg'),
            'label' => '',
            'value' => Application::VERSION,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        $debug = Env::get('APP_DEBUG', false);

        $tooltip = $this->createTooltipGroup([
            'Profiler token' => '',
            'Application name' => '',
            'Environment' => Env::get('APP_ENV', 'develop'),
            'Debug' => [
                [
                    'class' => $debug !== false ? 'status-green' : 'status-red',
                    'value' => $debug !== false ? 'enabled' : 'disabled',
                ],
            ],
        ]);

        $tooltip .= $this->createTooltipGroup([
            'PHP version' => phpversion(),
            'Architecture' => PHP_INT_SIZE * 8,
            'Timezone' => date_default_timezone_get(),
            'PHP Extensions' => [
                [
                    'class' => extension_loaded('xdebug') ? 'status-green' : 'status-red',
                    'value' => 'Xdebug',
                ],
                [
                    'class' => (extension_loaded('Zend OPcache') && ini_get('opcache.enable')) ? 'status-green' : 'status-red',
                    'value' => 'OPcache',
                ],
            ],
            'PHP SAPI' => php_sapi_name(),
        ]);

        $tooltip .= $this->createTooltipGroup([
            'Resources' => '',
            'Help' => '',
        ]);

        return $tooltip;
    }
}
