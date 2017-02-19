<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\DataCollectors\Bridge;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface;
use Viserio\Component\WebProfiler\DataCollectors\AbstractDataCollector;

class ClockworkDataCollector extends AbstractDataCollector
{
    /**
     * @param \SessionHandlerInterface $session
     */
    public function __construct(SessionHandlerInterface $session)
    {
        // code...
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        // all collecting is done client side
    }
}
