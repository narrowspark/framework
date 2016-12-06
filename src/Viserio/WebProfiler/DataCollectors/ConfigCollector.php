<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

class ConfigCollector implements TabAwareContract, DataCollectorContract
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
    public function __construct(array $data = [], $name = 'config')
    {
        $this->data = $data;
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
    public function getTabPosition(): string
    {
        return 'left';
    }

    /**
     * {@inheritdoc}
     */
    public function getTab(): array
    {
        $memory = $this->data['memory'] / 1024 / 1024;

        return [
            'icon' => file_get_contents(__DIR__ . '/../Resources/icons/ic_settings_applications_white_24px.svg'),
            'label' => 'Configs',
            'count' => count($this->data),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'config';
    }
}
