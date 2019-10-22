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

namespace Viserio\Component\WebServer\Event;

use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\Server\Connection;
use Symfony\Component\VarDumper\VarDumper;

class DumpListenerEvent
{
    /**
     * A cloner instance.
     *
     * @var \Symfony\Component\VarDumper\Cloner\ClonerInterface
     */
    private $cloner;

    /**
     * A DataDumper instance.
     *
     * @var \Symfony\Component\VarDumper\Dumper\DataDumperInterface
     */
    private $dumper;

    /**
     * A Connection instance.
     *
     * @var null|\Symfony\Component\VarDumper\Server\Connection
     */
    private $connection;

    /**
     * Create a new DumpListenerEvent instance.
     *
     * @param \Symfony\Component\VarDumper\Cloner\ClonerInterface     $cloner
     * @param \Symfony\Component\VarDumper\Dumper\DataDumperInterface $dumper
     * @param null|\Symfony\Component\VarDumper\Server\Connection     $connection
     */
    public function __construct(ClonerInterface $cloner, DataDumperInterface $dumper, Connection $connection = null)
    {
        $this->cloner = $cloner;
        $this->dumper = $dumper;
        $this->connection = $connection;
    }

    public function configure(): void
    {
        $cloner = $this->cloner;
        $dumper = $this->dumper;
        $connection = $this->connection;

        VarDumper::setHandler(static function ($var) use ($cloner, $dumper, $connection): void {
            $data = $cloner->cloneVar($var);

            if (! $connection || ! $connection->write($data)) {
                $dumper->dump($data);
            }
        });
    }
}
