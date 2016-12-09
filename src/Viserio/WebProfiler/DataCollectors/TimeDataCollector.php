<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

class TimeDataCollector extends AbstractDataCollector
{
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
        return 'time';
    }
}
