<?php
namespace Viserio\Database\Exception;

use Viserio\Support\Helper;

class ConnectException extends \PDOException
{
    /**
     * The SQL for the query.
     *
     * @var string
     */
    protected $sql;

    /**
     * The bindings for the query.
     *
     * @var array
     */
    protected $bindings;

    /**
     * Instance of \Exception.
     *
     * @var \Exception
     */
    protected $previous;

    /**
     * Create a new query exception instance.
     *
     * @param string     $sql
     * @param array      $bindings
     * @param \Exception $previous
     */
    public function __construct($sql, $bindings, $previous)
    {
        parent::__construct('', 0, $previous);

        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->previous = $previous;
        $this->code = $previous->getCode();
        $this->message = $this->formatMessage($sql, $bindings, $previous);

        if ($previous instanceof \PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * Format the SQL error message.
     *
     * @param string     $sql
     * @param array      $bindings
     * @param \Exception $previous
     *
     * @return string
     */
    protected function formatMessage($sql, $bindings, $previous)
    {
        return $previous->getMessage().' (SQL: '.Helper::strReplaceArray('\?', $bindings, $sql).')';
    }

    /**
     * Get the SQL for the query.
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Get the bindings for the query.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }
}
