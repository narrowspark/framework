<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Configuration;

use Cache\Bridge\Doctrine\DoctrineCacheBridge;
use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\ORM\Configuration\CacheManager;

/**
 * @internal
 */
final class CacheManagerTest extends TestCase
{
    public function testGetDriverWithDoctrineWrapper(): void
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
                        ],
                    ],
                ],
            ])
        );

        $this->assertInstanceOf(DoctrineCacheBridge::class, $manager->getDriver('array'));
        $this->assertInstanceOf(DoctrineCacheBridge::class, $manager->getDriver());
    }
}
