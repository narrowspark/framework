<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Fixture;

use PDO;

class MockPdo extends PDO
{
    public $prepareResult;
    private $driverName;
    private $errorMode;

    public function __construct($driverName = null, $errorMode = null)
    {
        $this->driverName = $driverName;
        $this->errorMode  = null !== $errorMode ?: PDO::ERRMODE_EXCEPTION;
    }

    public function getAttribute($attribute)
    {
        if (PDO::ATTR_ERRMODE === $attribute) {
            return $this->errorMode;
        }

        if (PDO::ATTR_DRIVER_NAME === $attribute) {
            return $this->driverName;
        }

        return parent::getAttribute($attribute);
    }

    public function prepare($statement, $driverOptions = [])
    {
        return is_callable($this->prepareResult)
        ? call_user_func($this->prepareResult, $statement, $driverOptions)
        : $this->prepareResult;
    }

    public function beginTransaction(): void
    {
    }

    public function rollBack(): void
    {
    }
}
