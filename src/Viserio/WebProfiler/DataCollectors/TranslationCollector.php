<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

class TranslationCollector extends AbstractDataCollector
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
        return [
            'translations' => [
                'icon' => 'leaf',
                'tooltip' => 'Translations',
                'widget' => 'PhpDebugBar.Widgets.TranslationsWidget',
                'map' => 'translations',
                'default' => '[]',
            ],
            'translations:badge' => [
                'map' => 'translations.nb_translations',
                'default' => 0,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets()
    {
        return [
            'base_path' => __DIR__ . '/../Resources',
            'css' => 'widgets/translations/widget.css',
            'js' => 'widgets/translations/widget.js',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $translations = $this->translations;

        return [
            'nb_translations' => count($translations),
            'translations' => $translations,
        ];
    }
}
