<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Configuration;

use Viserio\Bridge\Doctrine\ORM\Configuration\CacheManager;
use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Cache\Bridge\Doctrine\DoctrineCacheBridge;

class CacheManagerTest extends TestCase
{
    public function testGetDriverWithDoctrineWrapper()
    {
        $manager = new CacheManager(
            new ArrayContainer([
                'config' => [
                    'viserio' => [
                        'doctrine' => [
                            'cache' => [
                                'drivers'   => [],
                                'namespace' => false,
                            ],
                        ]
                    ]
                ]
            ])
        );

        self::assertInstanceOf(DoctrineCacheBridge::class, $manager->getDriver('array'));
    }
}
