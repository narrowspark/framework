<?php
declare(strict_types=1);
namespace Viserio\Database;

use PDOException as BasePDOException;
use PDOStatement as BasePDOStatement;
use Viserio\Contracts\Database\Exception\PDOException;
use Viserio\Contracts\Database\Statement as StatementContract;

class PDOStatement extends BasePDOStatement implements StatementContract
{
    /**
     * Protected constructor.
     */
    protected function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
    {
        // This thin wrapper is necessary to shield against the weird signature
        // of PDOStatement::setFetchMode(): even if the second and third
        // parameters are optional, PHP will not let us remove it from this
        // declaration.
        try {
            if ($arg2 === null && $arg3 === null) {
                return parent::setFetchMode($fetchMode);
            }

            if ($arg3 === null) {
                return parent::setFetchMode($fetchMode, $arg2);
            }

            return parent::setFetchMode($fetchMode, $arg2, $arg3);
        } catch (BasePDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bindValue($param, $value, $type = \PDO::PARAM_STR)
    {
        try {
            return parent::bindValue($param, $value, $type);
        } catch (BasePDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bindParam($column, &$variable, $type = \PDO::PARAM_STR, $length = null, $driverOptions = null)
    {
        try {
            return parent::bindParam($column, $variable, $type, $length, $driverOptions);
        } catch (BasePDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute($params = null)
    {
        try {
            return parent::execute($params);
        } catch (BasePDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchMode = null, $cursorOrientation = null, $cursorOffset = null)
    {
        try {
            if ($fetchMode === null && $cursorOrientation === null && $cursorOffset === null) {
                return parent::fetch();
            }

            if ($cursorOrientation === null && $cursorOffset === null) {
                return parent::fetch($fetchMode);
            }

            if ($cursorOffset === null) {
                return parent::fetch($fetchMode, $cursorOrientation);
            }

            return parent::fetch($fetchMode, $cursorOrientation, $cursorOffset);
        } catch (BasePDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null)
    {
        try {
            if ($fetchMode === null && $fetchArgument === null && $ctorArgs === null) {
                return parent::fetchAll();
            }

            if ($fetchArgument === null && $ctorArgs === null) {
                return parent::fetchAll($fetchMode);
            }

            if ($ctorArgs === null) {
                return parent::fetchAll($fetchMode, $fetchArgument);
            }

            return parent::fetchAll($fetchMode, $fetchArgument, $ctorArgs);
        } catch (BasePDOException $exception) {
            throw new PDOException($exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn($columnIndex = 0)
    {
        try {
            return parent::fetchColumn($columnIndex);
        } catch (BasePDOException $exception) {
            throw new PDOException($exception);
        }
    }
}
