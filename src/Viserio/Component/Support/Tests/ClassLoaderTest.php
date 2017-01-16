<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\ClassLoader;

class ClassLoaderTest extends TestCase
{
    public function testLoad()
    {
        ClassLoader::addDirectories([__DIR__ . '/..']);

        self::assertTrue(ClassLoader::load('Str'));
        self::assertFalse(ClassLoader::load('NoExistManager'));
    }

    public function testNormalizeClass()
    {
        self::assertSame('TestManager.php', ClassLoader::normalizeClass('\\TestManager'));
        self::assertSame('Test/Manager.php', ClassLoader::normalizeClass('Test_Manager'));
    }

    public function testAddAndRemoeveDirectories()
    {
        ClassLoader::addDirectories([__DIR__ . '/Fixture']);
        ClassLoader::addDirectories([__DIR__ . '/Traits']);

        self::assertTrue(in_array(
            __DIR__ . '/Traits',
            ClassLoader::getDirectories()
        ));

        self::assertTrue(in_array(
            __DIR__ . '/Fixture',
            ClassLoader::getDirectories()
        ));

        ClassLoader::removeDirectories([__DIR__ . '/Fixture']);

        self::assertFalse(in_array(
            __DIR__ . '/Fixture',
            ClassLoader::getDirectories()
        ));

        ClassLoader::addDirectories([__DIR__ . '/Fixture']);
        ClassLoader::removeDirectories();

        self::assertSame([], ClassLoader::getDirectories());
    }
}
