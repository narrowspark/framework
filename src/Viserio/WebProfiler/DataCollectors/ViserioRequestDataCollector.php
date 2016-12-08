<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Contracts\WebProfiler\AssetAware as AssetAwareContract;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;

class ViserioRequestDataCollector extends AbstractDataCollector implements TabAwareContract, TooltipAwareContract, AssetAwareContract, PanelAwareContract
{
    /**
     * A server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $serverRequest;
    /**
     * A response instance.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * List of all request sessions.
     *
     * @var array
     */
    protected $sessions;

    /**
     * Current route.
     *
     * @var \Viserio\Contracts\Routing\Route
     */
    protected $route;

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $this->serverRequest = $serverRequest;
        $this->response = $response;

        $sessions = [];

        foreach ($this->serverRequest->getAttributes() as $name => $value) {
            if ($value instanceof StoreContract) {
                $sessions[] = $value;
            }

            if ($value instanceof RouteContract) {
                $this->route = $value;
            }
        }

        return $this->sessions = $sessions;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'viserio-request';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabPosition(): string
    {
        return 'left';
    }

    /**
     * {@inheritdoc}
     */
    public function getTab(): array
    {
        $statusCode = $this->response->getStatusCode();
        $status = '';

        // Successful 2xx
        if ($statusCode > 200 && $statusCode < 226) {
            $status = 'request-status-green';
        // Redirection 3xx
        } elseif ($statusCode > 300 && $statusCode < 308) {
            $status = 'request-status-yellow';
        // Client Error 4xx
        } elseif ($statusCode > 400 && $statusCode < 511) {
            $status = 'request-status-red';
        }

        return [
            'status' => $statusCode,
            'class' => $status,
            'label' => '@',
            'value' => $this->route->getName(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        $html = '';

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $html = '';

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets(): array
    {
        return [
            'css' => __DIR__ . '/../Resources/css/widgets/viserio/request.css';
        ];
    }
}
