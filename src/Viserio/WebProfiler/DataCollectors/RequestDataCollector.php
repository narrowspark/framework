<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Contracts\Routing\Route as RouteContract;

class RequestDataCollector extends AbstractDataCollector implements TabAwareContract, TooltipAwareContract
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
    public function collect(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;

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
        return 'request';
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
        $route = $this->getRequestRoute();

        return [
            'icon' => '',
            'label' => '',
            'value' => '',
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

    protected function getRequestRoute()
    {
        foreach ($this->serverRequest->getAttributes() as $name => $value) {
            if ($value instanceof RouteContract) {
                return $value;
            }
        }
    }

    /**
     * Get request sessions.
     *
     * @return array
     */
    protected function getRequestSession(): array
    {
        $sessions = [];

        foreach ($this->serverRequest->getAttributes() as $name => $value) {
            if ($value instanceof StoreContract) {
                $sessions[] = $value;
            }
        }

        return $sessions;
    }
}
