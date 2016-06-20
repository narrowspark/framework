<?php
namespace Viserio\Support\Tests;

use Viserio\Support\ClassLoader;

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        ClassLoader::addDirectories([__DIR__ . '/..']);

        $this->assertTrue(ClassLoader::load('Str'));
        $this->assertFalse(ClassLoader::load('NoExistManager'));
    }

    public function testNormalizeClass()
    {
        $this->assertSame('TestManager.php', ClassLoader::normalizeClass('\\TestManager'));
        $this->assertSame('Test/Manager.php', ClassLoader::normalizeClass('Test_Manager'));
    }

    public function testAddAndRemoeveDirectories()
    {
        ClassLoader::addDirectories([__DIR__ . '/Fixture']);
        ClassLoader::addDirectories([__DIR__ . '/Traits']);

        $this->assertTrue(in_array(
            __DIR__ . '/Traits',
            ClassLoader::getDirectories()
        ));

        $this->assertTrue(in_array(
            __DIR__ . '/Fixture',
            ClassLoader::getDirectories()
        ));

        ClassLoader::removeDirectories([__DIR__ . '/Fixture']);

        $this->assertFalse(in_array(
            __DIR__ . '/Fixture',
            ClassLoader::getDirectories()
        ));

        ClassLoader::addDirectories([__DIR__ . '/Fixture']);
        ClassLoader::removeDirectories();

        $this->assertSame([], ClassLoader::getDirectories());
    }
}
