<?php declare(strict_types=1);
namespace LaravelDoctrine\Migrations\Naming;

use Doctrine\DBAL\Migrations\Finder\MigrationFinderInterface;
use Doctrine\DBAL\Migrations\Finder\RecursiveRegexFinder;
use Viserio\Bridge\Doctrine\Contract\Migration\NamingStrategy as NamingStrategyContract;

class DefaultNamingStrategy implements NamingStrategyContract
{
    /**
     * {@inheritdoc}
     */
    public function getFilename(?string $version = null): string
    {
        $version = $version ?? \date('YmdHis');

        return 'Version' . $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(?string $version = null): string
    {
        $version = $version ?? \date('YmdHis');

        return 'Version' . $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinder(): MigrationFinderInterface
    {
        return new RecursiveRegexFinder();
    }
}
