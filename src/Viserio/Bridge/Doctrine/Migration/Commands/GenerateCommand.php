<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Commands;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Viserio\Bridge\Doctrine\Contract\Migration\Exception\InvalidArgumentException;

class GenerateCommand extends AbstractCommand
{
    protected function getMigrationDirectory(Configuration $configuration)
    {
        $dir = $configuration->getMigrationsDirectory();
        $dir = $dir ?? getcwd();
        $dir = rtrim($dir, '/');

        if ( ! file_exists($dir)) {
            throw new InvalidArgumentException(sprintf('Migrations directory [%s] does not exist.', $dir));
        }

        if ($configuration->areMigrationsOrganizedByYear()) {
            $dir .= DIRECTORY_SEPARATOR . date('Y');
        }

        if ($configuration->areMigrationsOrganizedByYearAndMonth()) {
            $dir .= DIRECTORY_SEPARATOR . date('m');
        }

        if ( ! file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }
}