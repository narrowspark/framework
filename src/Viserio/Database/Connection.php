<?php
declare(strict_types=1);
namespace Viserio\Database;

use Closure;
use PDO;
use RuntimeException;
use LogicException;
use Viserio\Contracts\Database\Connection as ConnectionContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Database\Traits\DetectsDeadlocksTrait;
use Viserio\Database\Traits\DetectsLostConnectionsTrait;

class Connection implements ConnectionContract
{
    use DetectsDeadlocksTrait;
    use DetectsLostConnectionsTrait;
    use EventsAwareTrait;

     /**
     * The active PDO connection.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * The active PDO connection used for reads.
     *
     * @var \PDO
     */
    protected $readPdo;

    /**
     * The reconnector instance for the connection.
     *
     * @var callable
     */
    protected $reconnector;

    /**
     * The number of active transactions.
     *
     * @var int
     */
    protected $transactions = 0;

    /**
     * The default fetch mode of the connection.
     *
     * @var int
     */
    protected $fetchMode = PDO::FETCH_ASSOC;

    /**
     * Get the default fetch mode for the connection.
     *
     * @return int
     */
    public function getFetchMode(): int
    {
        return $this->fetchMode;
    }

    /**
     * Disconnect from the underlying PDO connection.
     */
    public function disconnect()
    {
        $this->setPdo(null)->setReadPdo(null);
    }

    /**
     * Reconnect to the database.
     *
     * @throws \LogicException
     */
    public function reconnect()
    {
        if (is_callable($this->reconnector)) {
            return call_user_func($this->reconnector, $this);
        }

        throw new LogicException('Lost connection and no reconnector available.');
    }

    /**
     * Get the current PDO connection used for reading.
     *
     * @return \PDO
     */
    public function getReadPdo(): PDO
    {
        if ($this->transactions >= 1) {
            return $this->getPdo();
        }

        if ($this->readPdo instanceof Closure) {
            return $this->readPdo = call_user_func($this->readPdo);
        }

        return $this->readPdo ?: $this->getPdo();
    }

    /**
     * Set the PDO connection used for reading.
     *
     * @param \PDO|\Closure|null $pdo
     *
     * @return $this
     */
    public function setReadPdo($pdo): ConnectionContract
    {
        $this->readPdo = $pdo;

        return $this;
    }

    /**
     * Set the PDO connection.
     *
     * @param \PDO|\Closure|null $pdo
     *
     * @return $this
     *
     * @throws \RuntimeException
     */
    public function setPdo($pdo): ConnectionContract
    {
        if ($this->transactions >= 1) {
            throw new RuntimeException('Can\'t swap PDO instance while within transaction.');
        }

        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getPdo(): PDO
    {
        if ($this->pdo instanceof Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }

        return $this->pdo;
    }
}
