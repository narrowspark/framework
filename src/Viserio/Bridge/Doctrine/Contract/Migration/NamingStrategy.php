<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Contract\Migration;

use Doctrine\DBAL\Migrations\Finder\MigrationFinderInterface;

interface NamingStrategy
{
    /**
     *
     * @param null|string $version
     *
     * @return string
     */
    public function getFilename(?string $version = null): string;

    /**
     *
     * @param null|string $version
     *
     * @return string
     */
    public function getClassName(?string $version = null): string;

    /**
     *
     * @return \Doctrine\DBAL\Migrations\Finder\MigrationFinderInterface
     */
    public function getFinder(): MigrationFinderInterface;
}
