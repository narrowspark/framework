<?php
declare(strict_types=1);
namespace Viserio\Component\Log\DataCollectors;

use Viserio\Component\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Component\WebProfiler\DataCollectors\MessagesDataCollector;

class LogsDataCollector extends MessagesDataCollector implements PanelAwareContract
{
    /**
     * LogParser instance.
     *
     * @var \Viserio\Component\Log\DataCollectors\LogParser
     */
    protected $logParser;

    /**
     * All places for log files.
     *
     * @var array
     */
    protected $storages = [];

    /**
     * Create a new logs data collector instance.
     *
     * @param \Viserio\Component\Log\DataCollectors\LogParser $logParser
     * @param string|array                                    $storages
     */
    public function __construct(LogParser $logParser, $storages)
    {
        parent::__construct('logs');

        $this->logParser = $logParser;
        $this->storages  = (array) $storages;
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

        foreach ($this->data['messages'] as $file) {
            $name = '';

            foreach ($this->storages as $storage) {
                $name = $this->stripBasePath($storage, $file);
            }

            $logs[str_replace('.log', '', $name)] = $this->createTable(
                $this->logParser->parse($file),
                ['headers' => ['Type', 'Message']]
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
     * {@inheritdoc}
     */
    public function getMessages(): array
    {
        $files = [];

        foreach ($this->storages as $storage) {
            $files = array_merge($files, glob($storage . '*.{log,txt}', GLOB_BRACE));
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
