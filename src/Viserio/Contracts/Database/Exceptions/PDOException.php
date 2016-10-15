<?php
declare(strict_types=1);
namespace Viserio\Contracts\Database\Exceptions;

use PDOException as BasePDOException;

class PDOException extends BasePDOException
{
    /**
     * The driver specific error code.
     *
     * @var int|string|null
     */
    private $errorCode;

    /**
     * The SQLSTATE of the driver.
     *
     * @var string|null
     */
    private $sqlState;

    /**
     * Constructor.
     *
     * @param \PDOException $exception The PDO exception to wrap.
     */
    public function __construct(BasePDOException $exception)
    {
        parent::__construct($exception->getMessage(), 0, $exception);

        $this->code = $exception->getCode();
        $this->errorInfo = $exception->errorInfo;
        $this->errorCode = $exception->errorInfo[1] ?? $exception->getCode();
        $this->sqlState = $exception->errorInfo[0] ?? $exception->getCode();
    }

    /**
     * Returns the driver specific error code if available.
     *
     * Returns null if no driver specific error code is available
     * for the error raised by the driver.
     *
     * @return int|string|null
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Returns the SQLSTATE the driver was in at the time the error occurred.
     *
     * Returns null if the driver does not provide a SQLSTATE for the error occurred.
     *
     * @return string|null
     */
    public function getSQLState()
    {
        return $this->sqlState;
    }
}
