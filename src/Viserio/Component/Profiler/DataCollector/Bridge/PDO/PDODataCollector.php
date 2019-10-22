<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
