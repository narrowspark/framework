<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Bridge\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\WebProfiler\DataCollectors\AbstractDataCollector;

class PdoDataCollector extends AbstractDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
    }
}
