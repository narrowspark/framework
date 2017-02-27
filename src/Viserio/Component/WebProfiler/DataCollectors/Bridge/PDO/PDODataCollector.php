<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\DataCollectors\Bridge\PDO;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\WebProfiler\DataCollectors\AbstractDataCollector;

class PDODataCollector extends AbstractDataCollector
{
    /**
     * A Pdo decorater instance.
     *
     * @var \Viserio\Component\WebProfiler\DataCollectors\Bridge\PDO\TraceablePDODecorater
     */
    protected $pdoDecorater;

    /**
     * Create a new PDO collector instance.
     *
     * @param \Viserio\Component\WebProfiler\DataCollectors\Bridge\PDO\TraceablePDODecorater $pdoDecorater
     */
    public function __construct(TraceablePDODecorater $pdoDecorater)
    {
        $this->pdoDecorater = $pdoDecorater;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => '',
            'label' => '',
            'value' => '',
        ];
    }
}
