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

use PDO;
use PDOException;
use PDOStatement;

class TraceablePDODecorater extends PDO
{
    /**
     * PDO instance.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * All executed statements.
     *
     * @var array
     */
    protected $executedStatements = [];

    /**
     * Create a new TraceablePDODecorater instance.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [TraceablePDOStatementDecorater::class, [$this]]);
    }

    /**
     * Returns the list of executed statements as TracedStatement objects.
     */
    public function getExecutedStatements(): array
    {
        return $this->executedStatements;
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function errorCode()
    {
        return $this->pdo->errorCode();
    }

    /**
     * {@inheritdoc}
     */
    public function errorInfo(): array
    {
        return $this->pdo->errorInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function exec($statement)
    {
        return $this->profileCall('exec', $statement, \func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($attribute)
    {
        return $this->pdo->getAttribute($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null): string
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($statement, $driver_options = []): PDOStatement
    {
        return $this->pdo->prepare($statement, $driver_options);
    }

    /**
     * {@inheritdoc}
     */
    public function query($statement)
    {
        return $this->profileCall('query', $statement, \func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function quote($string, $parameter_type = PDO::PARAM_STR): string
    {
        return $this->pdo->quote($string, $parameter_type);
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($attribute, $value): bool
    {
        return $this->pdo->setAttribute($attribute, $value);
    }

    /**
     * Adds an executed TracedStatement.
     *
     * @param \Viserio\Component\Profiler\DataCollector\Bridge\PDO\TracedStatement $stmt
     */
    public function addExecutedStatement(TracedStatement $stmt): void
    {
        $this->executedStatements[] = $stmt;
    }

    /**
     * Returns the accumulated execution time of statements.
     */
    public function getAccumulatedStatementsDuration(): int
    {
        return \array_reduce($this->executedStatements, static function ($v, $s) {
            return $v + $s->getDuration();
        });
    }

    /**
     * Returns the peak memory usage while performing statements.
     */
    public function getMemoryUsage(): int
    {
        return \array_reduce($this->executedStatements, static function ($v, $s) {
            return $v + $s->getMemoryUsage();
        });
    }

    /**
     * Returns the peak memory usage while performing statements.
     */
    public function getPeakMemoryUsage(): int
    {
        return \array_reduce($this->executedStatements, static function ($v, $s) {
            $m = $s->getEndMemory();

            return $m > $v ? $m : $v;
        });
    }

    /**
     * Returns the list of failed statements.
     */
    public function getFailedExecutedStatements(): array
    {
        return \array_filter($this->executedStatements, static function ($s) {
            return ! $s->isSuccess();
        });
    }

    /**
     * Profiles a call to a PDO method.
     */
    protected function profileCall(string $method, string $sql, array $args)
    {
        $trace = new TracedStatement($sql);
        $trace->start();

        $ex = null;
        $result = null;

        try {
            $result = $this->pdo->{$method}(...$args);
        } catch (PDOException $e) {
            $ex = $e;
        }

        if ($this->pdo->getAttribute(PDO::ATTR_ERRMODE) !== PDO::ERRMODE_EXCEPTION && $result === false) {
            $error = $this->pdo->errorInfo();
            $ex = new PDOException($error[2], $error[0]);
        }

        $trace->end($ex);

        $this->addExecutedStatement($trace);

        if ($this->pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION && $ex !== null) {
            throw $ex;
        }

        return $result;
    }
}
