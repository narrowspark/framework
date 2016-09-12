<?php
declare(strict_types=1);
namespace Viserio\Database\Providers;

use Interop\Container\ContainerInterface;
use Viserio\Contracts\Config\Manager as ManagerContract;
use Interop\Container\ServiceProvider;

class DatabaseServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [];
    }

    public static function createRuntimeConfiguration(ContainerInterface $container)
    {
        $config = $container->get(ManagerContract::class)->get('database');

    }
}
