<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge\PDO;

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
     * @param array  $params
     * @param string $preparedId
     */
    public function __construct($sql, array $params = [], $preparedId = null)
    {
        $this->sql        = $sql;
        $this->parameters = $this->checkParameters($params);
        $this->preparedId = $preparedId;
    }

    /**
     * @param null $startTime
     * @param null $startMemory
     */
    public function start($startTime = null, $startMemory = null): void
    {
        $this->startTime   = $startTime ?: \microtime(true);
        $this->startMemory = $startMemory ?: \memory_get_usage(true);
    }

    /**
     * @param null|\Exception $exception
     * @param int             $rowCount
     * @param null            $endTime
     * @param null            $endMemory
     */
    public function end(\Exception $exception = null, $rowCount = 0, $endTime = null, $endMemory = null): void
    {
        $this->endTime     = $endTime ?: \microtime(true);
        $this->duration    = $this->endTime - $this->startTime;
        $this->endMemory   = $endMemory ?: \memory_get_usage(true);
        $this->memoryDelta = $this->endMemory - $this->startMemory;
        $this->exception   = $exception;
        $this->rowCount    = $rowCount;
    }

    /**
     * Check parameters for illegal (non UTF-8) strings, like Binary data.
     *
     * @param array $params
     *
     * @return mixed
     */
    public function checkParameters(array $params)
    {
        foreach ($params as &$param) {
            if (! \mb_check_encoding($param, 'UTF-8')) {
                $param = '[BINARY DATA]';
            }
        }

        return $params;
    }

    /**
     * Returns the SQL string used for the query.
     *
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Returns the SQL string with any parameters used embedded.
     *
     * @param string $quotationChar
     *
     * @return string
     */
    public function getSqlWithParams($quotationChar = '<>'): string
    {
        if (($l = \mb_strlen($quotationChar)) > 1) {
            $quoteLeft  = \mb_substr($quotationChar, 0, $l / 2);
            $quoteRight = \mb_substr($quotationChar, $l / 2);
        } else {
            $quoteLeft = $quoteRight = $quotationChar;
        }
        $sql = $this->sql;
        foreach ($this->parameters as $k => $v) {
            $v = "$quoteLeft$v$quoteRight";
            if (! \is_numeric($k)) {
                $sql = \str_replace($k, $v, $sql);
            } else {
                $p   = \mb_strpos($sql, '?');
                $sql = \mb_substr($sql, 0, $p) . $v . \mb_substr($sql, $p + 1);
            }
        }

        return $sql;
    }

    /**
     * Returns the number of rows affected/returned.
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Returns an array of parameters used with the query.
     *
     * @return array
     */
    public function getParameters(): array
    {
        $params = [];
        foreach ($this->parameters as $name => $param) {
            $params[$name] = \htmlentities($param, ENT_QUOTES, 'UTF-8', false);
        }

        return $params;
    }

    /**
     * Returns the prepared statement id.
     *
     * @return string
     */
    public function getPreparedId(): string
    {
        return $this->preparedId;
    }

    /**
     * Checks if this is a prepared statement.
     *
     * @return bool
     */
    public function isPrepared(): bool
    {
        return $this->preparedId !== null;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Returns the duration in seconds of the execution.
     *
     * @return int
     */
    public function getDuration(): int
    {
        return $this->duration;
    }

    /**
     * @return mixed
     */
    public function getStartMemory()
    {
        return $this->startMemory;
    }

    /**
     * @return mixed
     */
    public function getEndMemory()
    {
        return $this->endMemory;
    }

    /**
     * Returns the memory usage during the execution.
     *
     * @return int
     */
    public function getMemoryUsage(): int
    {
        return $this->memoryDelta;
    }

    /**
     * Checks if the statement was successful.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->exception === null;
    }

    /**
     * Returns the exception triggered.
     *
     * @return \Exception
     */
    public function getException(): \Exception
    {
        return $this->exception;
    }

    /**
     * Returns the exception's code.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->exception !== null ? $this->exception->getCode() : 0;
    }

    /**
     * Returns the exception's message.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->exception !== null ? $this->exception->getMessage() : '';
    }
}
