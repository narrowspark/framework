<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;

class SimpleFixtureServiceProvider implements ServiceProviderContract
{
    public function getFactories(): array
    {
        return [
            'param'   => [SimpleFixtureServiceProvider::class, 'getParam'],
            'service' => function () {
                return new ServiceFixture();
            },
        ];
    }

    public function getExtensions()
    {
        return [
            'previous' => [SimpleFixtureServiceProvider::class, 'getPrevious'],
        ];
    }

    public static function getParam()
    {
        return 'value';
    }

    public static function getPrevious(ContainerInterface $container, $previous = null)
    {
        return $previous . $previous;
    }
}
