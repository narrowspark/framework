<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Formatters;

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
     * Create a new template manager instance.
     *
     * @param Symfony\Component\VarDumper\Cloner\VarCloner $cloner
     */
    public function __construct(VarCloner $cloner)
    {
        $this->cloner = $cloner;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record): array
    {
        $record['context'] = $this->cloner->cloneVar($record['context']);
        $record['extra']   = $this->cloner->cloneVar($record['extra']);

        return $record;
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records): array
    {
        foreach ($records as $k => $record) {
            $record[$k] = $this->format($record);
        }

        return $records;
    }
}
