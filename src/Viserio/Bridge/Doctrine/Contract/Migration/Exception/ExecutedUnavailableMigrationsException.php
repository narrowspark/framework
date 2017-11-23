<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Contract\Migration\Exception;

use Exception as BaseException;

class ExecutedUnavailableMigrationsException extends BaseException implements Exception
{
    /**
     * List of unavailable migrations.
     *
     * @var array
     */
    protected $migrations;

    /**
     * Create a new ExecutedUnavailableMigrationsException instance.
     *
     * @param array $migrations
     */
    public function __construct(array $migrations = [])
    {
        $this->migrations = $migrations;
    }

    /**
     * Get all unavailable migrations.
     *
     * @return array
     */
    public function getMigrations(): array
    {
        return $this->migrations;
    }
}