<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;

class SimpleFixtureServiceProvider implements ServiceProvider
{
    public function getServices()
    {
        return [
            'param'   => [SimpleFixtureServiceProvider::class, 'getParam'],
            'service' => function () {
                return new ServiceFixture();
            },
            'previous' => [SimpleFixtureServiceProvider::class, 'getPrevious'],
        ];
    }

    public static function getParam()
    {
        return 'value';
    }

    public static function getPrevious(ContainerInterface $container, callable $getPrevious = null)
    {
        return \is_callable($getPrevious) ? $getPrevious() : $getPrevious;
    }
}
