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

use Exception;

class TracedStatement
{
    protected $sql;

    protected $rowCount;

    protected $parameters;

    protected $startTime;

    protected $endTime;

    protected $duration;

    protected $startMemory;

    protected $endMemory;

    protected $memoryDelta;

    protected $exception;

    protected $preparedId;

    /**
     * @param string $sql
     * @param string $preparedId
     */
    public function __construct($sql, array $params = [], $preparedId = null)
    {
        $this->sql = $sql;
        $this->parameters = $this->checkParameters($params);
        $this->preparedId = $preparedId;
    }

    /**
     * Returns the SQL string used for the query.
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Returns the number of rows affected/returned.
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Returns an array of parameters used with the query.
     */
    public function getParameters(): array
    {
        $params = [];

        foreach ($this->parameters as $name => $param) {
            $params[$name] = \htmlentities($param, \ENT_QUOTES, 'UTF-8', false);
        }

        return $params;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Returns the duration in seconds of the execution.
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getStartMemory()
    {
        return $this->startMemory;
    }

    public function getEndMemory()
    {
        return $this->endMemory;
    }

    /**
     * Returns the exception triggered.
     */
    public function getException(): Exception
    {
        return $this->exception;
    }

    /**
     * Returns the prepared statement id.
     */
    public function getPreparedId(): string
    {
        return $this->preparedId;
    }

    /**
     * @param null $startTime
     * @param null $startMemory
     */
    public function start($startTime = null, $startMemory = null): void
    {
        $this->startTime = $startTime ?? \microtime(true);
        $this->startMemory = $startMemory ?? \memory_get_usage(true);
    }

    /**
     * @param int  $rowCount
     * @param null $endTime
     * @param null $endMemory
     */
    public function end(?Exception $exception = null, $rowCount = 0, $endTime = null, $endMemory = null): void
    {
        $this->endTime = $endTime ?? \microtime(true);
        $this->duration = $this->endTime - $this->startTime;
        $this->endMemory = $endMemory ?? \memory_get_usage(true);
        $this->memoryDelta = $this->endMemory - $this->startMemory;
        $this->exception = $exception;
        $this->rowCount = $rowCount;
    }

    /**
     * Check parameters for illegal (non UTF-8) strings, like Binary data.
     */
    public function checkParameters(array $params)
    {
        foreach ($params as &$param) {
            if (! \check_encoding($param, 'UTF-8')) {
                $param = '[BINARY DATA]';
            }
        }

        return $params;
    }

    /**
     * Returns the SQL string with any parameters used embedded.
     *
     * @param string $quotationChar
     */
    public function getSqlWithParams($quotationChar = '<>'): string
    {
        if (($l = \strlen($quotationChar)) > 1) {
            $quoteLeft = \substr($quotationChar, 0, $l / 2);
            $quoteRight = \substr($quotationChar, $l / 2);
        } else {
            $quoteLeft = $quoteRight = $quotationChar;
        }
        $sql = $this->sql;

        foreach ($this->parameters as $k => $v) {
            $v = "{$quoteLeft}{$v}{$quoteRight}";

            if (! \is_numeric($k)) {
                $sql = \str_replace($k, $v, $sql);
            } else {
                $p = \strpos($sql, '?');
                $sql = \substr($sql, 0, $p) . $v . \substr($sql, $p + 1);
            }
        }

        return $sql;
    }

    /**
     * Checks if this is a prepared statement.
     */
    public function isPrepared(): bool
    {
        return $this->preparedId !== null;
    }

    /**
     * Returns the memory usage during the execution.
     */
    public function getMemoryUsage(): int
    {
        return $this->memoryDelta;
    }

    /**
     * Checks if the statement was successful.
     */
    public function isSuccess(): bool
    {
        return $this->exception === null;
    }

    /**
     * Returns the exception's code.
     */
    public function getErrorCode(): string
    {
        return $this->exception !== null ? $this->exception->getCode() : 0;
    }

    /**
     * Returns the exception's message.
     */
    public function getErrorMessage(): string
    {
        return $this->exception !== null ? $this->exception->getMessage() : '';
    }
}
