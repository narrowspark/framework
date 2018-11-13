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
            'param'   => [__CLASS__, 'getParam'],
            'service' => function () {
                return new ServiceFixture();
            },
        ];
    }

    public function getExtensions()
    {
        return [
            'previous' => [__CLASS__, 'getPrevious'],
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
