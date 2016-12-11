<?php
declare(strict_types=1);
namespace Viserio\Foundation\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;

class ViserioConfigDataCollector extends AbstractDataCollector implements MenuAwareContract, PanelAwareContract
{
    /**
     * Collected data.
     *
     * @var array
     */
    protected $data;

    /**
     * @param array  $data
     * @param string $name
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'config';
    }

    /**
     * Sets the data.
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon' => file_get_contents(__DIR__ . '/Resources/icons/ic_settings_applications_white_24px.svg'),
            'label' => 'Configs',
            'value' => '',
        ];
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
     * Format config values.
     *
     * @return array
     */
    private function formatConfigs(): array
    {
        $data = [];

        foreach ($this->data as $key => $value) {
            if (! is_string($value)) {
                $value = $this->formatVar($value);
            }

            $data[$key] = $value;
        }

        return $data;
    }
}
