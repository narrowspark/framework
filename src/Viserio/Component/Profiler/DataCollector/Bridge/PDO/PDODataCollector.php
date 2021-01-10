<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Profiler\DataCollector\Bridge\PDO;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;

class PDODataCollector extends AbstractDataCollector
{
    /**
     * A Pdo decorater instance.
     *
     * @var \Viserio\Component\Profiler\DataCollector\Bridge\PDO\TraceablePDODecorater
     */
    protected $pdoDecorater;

    /**
     * Create a new PDO collector instance.
     *
     * @param \Viserio\Component\Profiler\DataCollector\Bridge\PDO\TraceablePDODecorater $pdoDecorater
     */
    public function __construct(TraceablePDODecorater $pdoDecorater)
    {
        $this->pdoDecorater = $pdoDecorater;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon' => '',
            'label' => '',
            'value' => '',
        ];
    }
}
