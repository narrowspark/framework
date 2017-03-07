<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Fluent\Tests\Providers;

use Doctrine\ORM\Configuration;
use LaravelDoctrine\Fluent\FluentDriver;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\DBAL\Providers\DoctrineDBALServiceProvider;
use Viserio\Bridge\Doctrine\Fluent\Providers\DoctrineFluentServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class DoctrineFluentServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());
        $container->register(new DoctrineDBALServiceProvider());
        $container->register(new DoctrineFluentServiceProvider());

        $container->instance('config', [
            'viserio' => [
                'doctrine' => [
                    'fluent' => [
                        'mappings' => [
                        ],
                    ],
                    'default'     => 'mysql',
                    'connections' => [
                        'mysql' => [
                            'driver'        => 'pdo_mysql',
                            'host'          => 'DB_HOST',
                            'port'          => 'DB_PORT',
                            'database'      => 'DB_DATABASE_NAME',
                            'username'      => 'DB_DATABASE_USER',
                            'password'      => 'DB_DATABASE_PASSWORD',
                            'charset'       => 'DB_CHARSET', 'UTF8',
                            'driverOptions' => [1002 => 'SET NAMES utf8'],
                        ],
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(FluentDriver::class, $container->get(FluentDriver::class));
        self::assertNull($container->get(Configuration::class));
    }
}
