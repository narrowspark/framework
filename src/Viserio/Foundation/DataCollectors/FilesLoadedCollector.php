<?php
declare(strict_types=1);
namespace Viserio\Foundation\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Foundation\Application;
use Viserio\Support\Env;
use Viserio\WebProfiler\DataCollectors\AbstractDataCollector;

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
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        // Get the files included on load.
        $includedFiles = get_included_files();
        $compiled = $this->getCompiledFiles();

        $included = [];
        $files = [];

        foreach ($includedFiles as $file) {
            // Skip the files from webprofiler, they are only loaded for Debugging and confuse the output.
            if (strpos($file, 'vendor/narrowspark/webprofiler') !== false || strpos($file, 'vendor/viserio/web-profiler') !== false) {
                continue;
            } elseif (!in_array($file, $compiled)) {
                $included[] = $files['included'][] = $this->stripBasePath($file);
            } else {
                $files['compiled'][] = $this->stripBasePath($file);
            }
        }

        $this->included = $included;
        $this->files = $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'label' => '',
            'value' => (string) count($this->included),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $included = $this->files['included'];
        $compiled = $this->files['compiled'];

        return $this->createTabs([
            [
                'name' => 'Included Files <span class="counter">' . count($included) . '</span>',
                'content' => $this->createTable(
                    $included,
                    '',
                    ['File']
                );
            ],
            [
                'name' => 'Compiled Files <span class="counter">' . count($compiled) . '</span>',
                'content' => $this->createTable(
                    $compiled,
                    '',
                    ['File']
                );
            ],
        ]);
    }

    /**
     * Get the files that are going to be compiled, so they aren't as important.
     *
     * @return array
     */
    protected function getCompiledFiles(): array
    {
        return [];
    }
}
