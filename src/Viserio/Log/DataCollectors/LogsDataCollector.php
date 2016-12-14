<?php
declare(strict_types=1);
namespace Viserio\Log\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Log\DataCollectors\LogParser;
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
     * @param array                                 $storages
     */
    public function __construct(LogParser $logParser, array $storages)
    {
        $this->logParser = $logParser;
        $this->storages = $storages;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'label' => 'Logs',
            'value' => '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $logs = [];

        foreach ($this->getLogsFiles() as $file) {
            $log = $this->logParser->parse($file);

            $logs[$log[2]] = $log;
        }

        $html = '';

        $html .= $this->createTable([
            $logs,
            '',
            [
                'Level',
                'Date',
                'Message',
            ],
        ]);

        return $html;
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
}
