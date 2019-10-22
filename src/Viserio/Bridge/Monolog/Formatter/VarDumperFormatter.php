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

namespace Viserio\Bridge\Monolog\Formatter;

use Monolog\Formatter\FormatterInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;

class VarDumperFormatter implements FormatterInterface
{
    /**
     * A VarCloner instance.
     *
     * @var \Symfony\Component\VarDumper\Cloner\VarCloner
     */
    private $cloner;

    /**
     * Create a new var dump formatter instance.
     *
     * @param \Symfony\Component\VarDumper\Cloner\VarCloner $cloner
     */
    public function __construct(VarCloner $cloner)
    {
        $this->cloner = $cloner;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $record['context'] = $this->cloner->cloneVar($record['context']);
        $record['extra'] = $this->cloner->cloneVar($record['extra']);

        return $record;
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }
}
