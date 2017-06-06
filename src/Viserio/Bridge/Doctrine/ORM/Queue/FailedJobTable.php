<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

class FailedJobTable extends AbstractTable
{
    /**
     * {@inheritdoc}
     */
    protected function getColumns(): array
    {
        return [
            $this->createColumn('id', 'integer', true),
            $this->createColumn('connection', 'string'),
            $this->createColumn('queue', 'string'),
            $this->createColumn('payload', 'text'),
            $this->createColumn('failed_at', 'datetime'),
            $this->createColumn('exception', 'text')->setNotnull(false),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIndices(): array
    {
        return [
            $this->index('pk', ['id'], true, true),
        ];
    }
}
