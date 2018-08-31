<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler;

use Viserio\Component\Contract\Profiler\AssetAware as AssetAwareContract;
use Viserio\Component\Contract\Profiler\AssetsRenderer as AssetsRendererContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class AssetsRenderer implements AssetsRendererContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * All css files.
     *
     * @var array
     */
    protected $cssFiles = [
        'css/profiler.css',
        'css/profiler-grid.css',
    ];

    /**
     * All js files.
     *
     * @var array
     */
    protected $jsFiles = [
        'js/zepto.min.js',
        'js/profiler.js',
    ];

    /**
     * List of all icons.
     *
     * @var array
     */
    protected $icons = [
        'ic_clear_white_24px.svg'               => __DIR__ . '/Resource/icons/ic_clear_white_24px.svg',
        'ic_memory_white_24px.svg'              => __DIR__ . '/Resource/icons/ic_memory_white_24px.svg',
        'ic_message_white_24px.svg'             => __DIR__ . '/Resource/icons/ic_message_white_24px.svg',
        'ic_narrowspark_white_24px.svg'         => __DIR__ . '/Resource/icons/ic_narrowspark_white_24px.svg',
        'ic_schedule_white_24px.svg'            => __DIR__ . '/Resource/icons/ic_schedule_white_24px.svg',
        'ic_storage_white_24px.svg'             => __DIR__ . '/Resource/icons/ic_storage_white_24px.svg',
        'ic_mail_outline_white_24px.svg'        => __DIR__ . '/Resource/icons/ic_mail_outline_white_24px.svg',
        'ic_keyboard_arrow_up_white_24px.svg'   => __DIR__ . '/Resource/icons/ic_keyboard_arrow_up_white_24px.svg',
        'ic_keyboard_arrow_down_white_24px.svg' => __DIR__ . '/Resource/icons/ic_keyboard_arrow_down_white_24px.svg',
        'ic_repeat_white_24px.svg'              => __DIR__ . '/Resource/icons/ic_repeat_white_24px.svg',
        'ic_layers_white_24px.svg'              => __DIR__ . '/Resource/icons/ic_layers_white_24px.svg',
        'ic_insert_drive_file_white_24px.svg'   => __DIR__ . '/Resource/icons/ic_insert_drive_file_white_24px.svg',
        'ic_library_books_white_24px.svg'       => __DIR__ . '/Resource/icons/ic_library_books_white_24px.svg',
    ];

    /**
     * The profiler instance.
     *
     * @var \Viserio\Component\Contract\Profiler\Profiler
     */
    protected $profiler;

    /**
     * Root path to the resource.
     *
     * @var string
     */
    protected $rootPath;

    /**
     * List of ignored collectors.
     *
     * @var array
     */
    protected $ignoredCollectors = [];

    /**
     * If jQuery is used, remove zapto.
     *
     * @var bool
     */
    protected $jqueryIsUsed;

    /**
     * Create a new file javascript renderer instance.
     *
     * @param bool        $jqueryIsUsed
     * @param null|string $rootPath
     */
    public function __construct(bool $jqueryIsUsed = false, string $rootPath = null)
    {
        $this->jqueryIsUsed = $jqueryIsUsed;
        $this->rootPath     = $rootPath ?? __DIR__ . '/Resource';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcons(): array
    {
        return $this->icons;
    }

    /**
     * {@inheritdoc}
     */
    public function setProfiler(ProfilerContract $profiler): AssetsRendererContract
    {
        $this->profiler = $profiler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIgnoredCollectors(): array
    {
        return $this->ignoredCollectors;
    }

    /**
     * {@inheritdoc}
     */
    public function setIcon(string $name, string $path): AssetsRendererContract
    {
        $this->icons[$name] = self::normalizePath($path . '/' . $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setIgnoredCollector(string $name): AssetsRendererContract
    {
        $this->ignoredCollectors[] = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        if (($urlGenerator = $this->profiler->getUrlGenerator()) !== null) {
            $cssRoute = $urlGenerator->generate('profiler.assets.css', [
                'v' => $this->getModifiedTime('css'),
            ]);
            $jsRoute = $urlGenerator->generate('profiler.assets.js', [
                'v' => $this->getModifiedTime('js'),
            ]);

            $html = \sprintf(
                '<link rel="stylesheet" type="text/css" property="stylesheet" href="%s">',
                \preg_replace('/\Ahttps?:/', '', $cssRoute)
            );
            $html .= \sprintf(
                '<script type="text/javascript" src="%s"></script>',
                \preg_replace('/\Ahttps?:/', '', $jsRoute)
            );

            return $html;
        }

        return $this->renderIntoHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function dumpAssetsToString(string $type): string
    {
        $files   = $this->getAssets($type);
        $content = '';

        foreach ($files as $file) {
            $content .= \file_get_contents($file) . "\n";
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets(?string $type = null): array
    {
        $cssFiles = \array_map(
            function ($css) {
                return \rtrim($this->rootPath, '/') . '/' . $css;
            },
            $this->cssFiles
        );

        if ($this->jqueryIsUsed) {
            $this->jsFiles = \array_diff($this->jsFiles, ['js/zepto.min.js']);
        }

        $jsFiles = \array_map(
            function ($js) {
                return \rtrim($this->rootPath, '/') . '/' . $js;
            },
            $this->jsFiles
        );

        $additionalAssets = \array_filter(
            \array_map(
                function ($collector) {
                    $collector = $collector['collector'];

                    if ($collector instanceof AssetAwareContract && ! \in_array($collector->getName(), $this->ignoredCollectors, true)) {
                        return $collector->getAssets();
                    }
                },
                $this->profiler->getCollectors()
            )
        );

        foreach ($additionalAssets as $assets) {
            if (isset($assets['css'])) {
                $cssFiles = \array_merge($cssFiles, (array) $assets['css']);
            }

            if (isset($assets['js'])) {
                $jsFiles = \array_merge($jsFiles, (array) $assets['js']);
            }
        }

        return $this->filterAssetArray([$cssFiles, $jsFiles], $type);
    }

    /**
     * Render css and js into html elements.
     *
     * @return string
     */
    protected function renderIntoHtml(): string
    {
        $html = \sprintf('<style>%s</style>', $this->dumpAssetsToString('css'));
        $html .= \sprintf('<script type="text/javascript">%s</script>', $this->dumpAssetsToString('js'));

        return $html;
    }

    /**
     * Filters a tuple of (css, js) assets according to $type.
     *
     * @param array       $array
     * @param null|string $type  'css', 'js' or null for both
     *
     * @return array
     */
    protected function filterAssetArray(array $array, ?string $type = null): array
    {
        if (\is_string($type)) {
            $type = \mb_strtolower($type);

            if ($type === 'css') {
                return $array[0];
            }

            if ($type === 'js') {
                return $array[1];
            }
        }

        return $array;
    }

    /**
     * Get the last modified time of any assets.
     *
     * @param string $type 'js' or 'css'
     *
     * @return int
     */
    protected function getModifiedTime(string $type): int
    {
        $files  = $this->getAssets($type);
        $latest = 0;

        foreach ($files as $file) {
            $mtime = (int) \filemtime($file);

            if ($mtime > $latest) {
                $latest = $mtime;
            }
        }

        return $latest;
    }
}
