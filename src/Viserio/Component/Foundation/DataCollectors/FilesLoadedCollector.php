<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Component\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Component\WebProfiler\DataCollectors\AbstractDataCollector;

class FilesLoadedCollector extends AbstractDataCollector implements
    MenuAwareContract,
    PanelAwareContract
{
    /**
     * All compiled and included files.
     *
     * @var array
     */
    protected $files = [];

    /**
     * All included files.
     *
     * @var array
     */
    protected $included = [];

    /**
     * Base path.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Create new files loaded collector instance.
     *
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        // Get the files included on load.
        $included = [];

        foreach (get_included_files() as $file) {
            // Skip the files from webprofiler, they are only loaded for Debugging and confuse the output.
            if (mb_strpos($file, 'vendor/narrowspark/framework/src/Viserio/WebProfiler') !== false ||
                mb_strpos($file, 'vendor/viserio/web-profiler') !== false
            ) {
                continue;
            }
            $included[] = $this->stripBasePath($file);
        }

        $this->included = $included;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => 'ic_insert_drive_file_white_24px.svg',
            'label' => '',
            'value' => (string) count($this->included),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        return $this->createTable(
            $this->included,
            ['headers' => ['Files']]
        );
    }

    /**
     * Remove the base path from the paths, so they are relative to the base.
     *
     * @param string $path
     *
     * @return string
     */
    protected function stripBasePath(string $path): string
    {
        return ltrim(str_replace($this->basePath, '', $path), '/');
    }
}
