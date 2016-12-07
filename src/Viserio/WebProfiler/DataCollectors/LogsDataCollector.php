<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Psr\Http\Message\ServerRequestInterface;

class LogsDataCollector extends AbstractDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'logs';
    }
}
