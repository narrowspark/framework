<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class TranslationCollector extends DataCollector implements Renderable, AssetProvider
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
    public function getName()
    {
        return 'views';
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgets()
    {
        return array(
            'translations' => array(
                'icon' => 'leaf',
                'tooltip' => 'Translations',
                'widget' => 'PhpDebugBar.Widgets.TranslationsWidget',
                'map' => 'translations',
                'default' => '[]'
            ),
            'translations:badge' => array(
                'map' => 'translations.nb_translations',
                'default' => 0
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets()
    {
        return array(
            'base_path' => __DIR__."/../Resources",
            'css' => 'widgets/translations/widget.css',
            'js' => 'widgets/translations/widget.js'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $translations = $this->translations;

        return array(
            'nb_translations' => count($translations),
            'translations' => $translations,
        );
    }
}
