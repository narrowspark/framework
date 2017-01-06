<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors\Bridge\PDO;

use PDO;

class TraceablePDODecorater extends PDO
{
    /**
     * PDO instance.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * [$executedStatements description].
     *
     * @var array
     */
    protected $executedStatements = [];

    /**
     * Create a new TraceablePDODecorater instance.
     *
     * @param \PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [TraceablePDOStatementDecorater::class, [$this]]);
    }

    /**
     * {@inhritdoc}.
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * {@inhritdoc}.
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * {@inhritdoc}.
     */
    public function errorCode()
    {
        return $this->pdo->errorCode();
    }

    /**
     * {@inhritdoc}.
     */
    public function errorInfo()
    {
        return $this->pdo->errorInfo();
    }

    /**
     * {@inhritdoc}.
     *
     * @param mixed $statement
     */
    public function exec($statement)
    {
        return $this->profileCall('exec', $statement, func_get_args());
    }

    /**
     * {@inhritdoc}.
     *
     * @param mixed $attribute
     */
    public function getAttribute($attribute)
    {
        return $this->pdo->getAttribute($attribute);
    }

    /**
     * {@inhritdoc}.
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    /**
     * {@inhritdoc}.
     *
     * @param null|mixed $name
     */
    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * {@inhritdoc}.
     * @param mixed $statement
     * @param mixed $driver_options
     */
    public function prepare($statement, $driver_options = [])
    {
        return $this->pdo->prepare($statement, $driver_options);
    }

    /**
     * {@inhritdoc}.
     *
     * @param mixed $statement
     */
    public function query($statement)
    {
        return $this->profileCall('query', $statement, func_get_args());
    }

    /**
     * {@inhritdoc}.
     *
     * @param mixed $string
     * @param mixed $parameter_type
     */
    public function quote($string, $parameter_type = PDO::PARAM_STR)
    {
        return $this->pdo->quote($string, $parameter_type);
    }

    /**
     * {@inhritdoc}.
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * {@inhritdoc}.
     *
     * @param mixed $attribute
     * @param mixed $value
     */
    public function setAttribute($attribute, $value)
    {
        return $this->pdo->setAttribute($attribute, $value);
    }
}
