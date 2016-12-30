<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors\Bridge\PDO;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\WebProfiler\DataCollectors\AbstractDataCollector;

class PDODataCollector extends AbstractDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
    }
}
