<?php
declare(strict_types=1);
namespace Viserio\Log\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\WebProfiler\DataCollectors\AbstractDataCollector;

class LogsDataCollector extends AbstractDataCollector implements
    MenuAwareContract,
    PanelAwareContract
{
    /**
     * LogParser instance.
     *
     * @var \Viserio\Log\DataCollectors\LogParser
     */
    protected $logParser;

    /**
     * Create a new logs data collector instance.
     *
     * @param \Viserio\Log\DataCollectors\LogParser $logParser
     * @param string|array                          $storages
     */
    public function __construct(LogParser $logParser, $storages)
    {
        $this->logParser = $logParser;
        $this->storages  = (array) $storages;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $this->data = [
            'logs' => $this->getLogsFiles(),
            'counted' => count($this->getLogsFiles()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'label' => 'Logs',
            'value' => $this->data['counted'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $html = '';
        $logs = [];

        foreach ($this->data['logs'] as $file) {
            foreach ($this->storages as $storage) {
                $name = $this->stripBasePath($storage, $file);
            }

            $logs[str_replace('.log', '', $name)] = $this->createTable(
                $this->logParser->parse($file),
                null,
                ['Type', 'Message']
            );
        }

        $html .= $this->createDropdownMenuContent($logs);

        return $html;
    }

    /**
     * Get counted logs.
     *
     * @return int
     */
    public function getCountedLogs(): int
    {
        return $this->data['counted'] ?? 0;
    }

    /**
     * Get all logs from given log storages.
     *
     * @return array
     */
    protected function getLogsFiles(): array
    {
        $files = [];

        foreach ($this->storages as $storage) {
            $files = array_merge($files, glob($storage));
        }

        $files = array_reverse($files);
        $files = array_filter($files, 'is_file');

        return array_values($files);
    }

    /**
     * Remove the base path from the paths, so they are relative to the base.
     *
     * @param string $storage
     * @param string $path
     *
     * @return string
     */
    protected function stripBasePath(string $storage, string $path): string
    {
        $storage = str_replace('*', '', $storage);

        return ltrim(str_replace($storage, '', $path), '/');
    }
}
