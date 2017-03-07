<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Viserio\Bridge\Doctrine\ORM\AbstractTable;

class FailedJobTable extends AbstractTable
{
    /**
     * {@inheritdoc}
     */
    protected function columns()
    {
        return [
            $this->column('id', 'integer', true),
            $this->column('connection', 'string'),
            $this->column('queue', 'string'),
            $this->column('payload', 'text'),
            $this->column('failed_at', 'datetime'),
            $this->column('exception', 'text')->setNotnull(false),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function indices()
    {
        return [
            $this->index('pk', ['id'], true, true)
        ];
    }
}
