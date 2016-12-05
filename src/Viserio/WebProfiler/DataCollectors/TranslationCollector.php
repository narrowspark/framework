<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Viserio\Contracts\WebProfiler\AssetAware as AssetAwareContract;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;

class TranslationCollector extends AbstractDataCollector implements AssetAwareContract, TabAwareContract
{
    /**
     * All translation for the actual page.
     *
     * @var array
     */
    protected $translations = [];

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
        return [
            'label' => '',
            'count' => count($this->translations),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets(): array
    {
        return [
            'css' => 'widgets/translations/widget.css',
            'js' => 'widgets/translations/widget.js',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'views';
    }
}
