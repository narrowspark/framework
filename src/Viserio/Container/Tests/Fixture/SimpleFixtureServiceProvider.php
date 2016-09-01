<?php
declare(strict_types=1);
namespace Viserio\Container\Tests\Fixture;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class SimpleFixtureServiceProvider implements ServiceProvider
{
    public function getServices()
    {
        return array(
            'param' => array(SimpleFixtureServiceProvider::class, 'getParam'),
            'service' => function() {
                return new ServiceFixture();
            },
            'previous' => array(SimpleFixtureServiceProvider::class, 'getPrevious'),
        );
    }

    public static function getParam()
    {
        return 'value';
    }

    public static function getPrevious(ContainerInterface $container, callable $getPrevious = null)
    {
        return $getPrevious;
    }
}
