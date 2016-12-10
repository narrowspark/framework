<?php
declare(strict_types=1);
namespace Viserio\Foundation\DataCollectors;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\WebProfiler\AssetAware as AssetAwareContract;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;

class ViserioTranslationDataCollector extends AbstractDataCollector implements AssetAwareContract, TabAwareContract
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
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'translations';
    }

    /**
     * {@inheritdoc}
     */
    public function getTab(): array
    {
        return [
            'label' => '',
            'value' => count($this->translations),
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
}
