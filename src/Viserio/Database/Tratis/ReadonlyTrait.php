<?php
declare(strict_types=1);
namespace Viserio\Database\Traits;

trait Readonly
{
    /**
     * @inheritDoc
     */
    public function delete(ConnectionInterface $con = null)
    {
        throw new PropelException("This is a readonly object.");
    }

    /**
     * @inheritDoc
     */
    public function save(ConnectionInterface $con = null)
    {
        throw new PropelException("This is a readonly object.");
    }
}
