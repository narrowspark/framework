<?php
namespace Viserio\Support\Tests;

use Viserio\Support\AliasLoader;

/**
 * AliasLoaderTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class AliasLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoaderCanBeCreatedAndRegisteredOnce()
    {
        $loader = AliasLoader::getInstance(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $loader->getAliases());
        $this->assertFalse($loader->isRegistered());
        $loader->register();
        $this->assertTrue($loader->isRegistered());
    }

    public function testGetInstanceCreatesOneInstance()
    {
        $loader = AliasLoader::getInstance(['foo' => 'bar']);
        $this->assertEquals($loader, AliasLoader::getInstance());
    }
}
