<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\StaticalProxy\AliasLoader;
use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Component\StaticalProxy\Tests\Fixture\Foo;

/**
 * @internal
 */
final class AliasLoaderTest extends TestCase
{
    public function testLiteral(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('TestFoo', Foo::class);

        $this->assertTrue($aliasloader->load('TestFoo'));
        $this->assertFalse($aliasloader->load('Unknown'));
    }

    public function testMatchedLiteral(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            'Tester\*' => Foo::class,
        ]);

        $this->assertTrue($aliasloader->load('Tester\ThisClass'));
        $this->assertFalse($aliasloader->load('Unknown\ThisClass'));
    }

    public function testMatchedReplacement(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            'Test\*' => 'Viserio\Component\StaticalProxy\Tests\Fixture\$1',
        ]);

        $this->assertTrue($aliasloader->load('Test\Foo'));
        $this->assertFalse($aliasloader->load('Test\Unknown'));
    }

    public function testNonExistingResolving(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('ThisClass', 'ToSomethingThatDoesntExist');

        $this->assertFalse($aliasloader->load('ThisClass'));
    }

    public function testAliasContainingTarget(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('FakeFoo::class', Foo::class);

        $this->assertTrue($aliasloader->load('FakeFoo::class'));
    }

    public function testRemoveloader(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->setAliases([
            'Resolvable'      => Foo::class,
            'ResolvableTwo'   => Foo::class,
            'ResolvableThree' => Foo::class,
            'ResolvableFour'  => Foo::class,
        ]);
        $this->assertInternalType('array', $aliasloader->getAliases());
        $this->assertTrue($aliasloader->load('Resolvable'));

        $aliasloader->removeAlias('ResolvableTwo');
        $this->assertFalse($aliasloader->load('ResolvableTwo'));

        $aliasloader->removeAlias('ResolvableThree');
        $this->assertFalse($aliasloader->load('ResolvableThree'));

        $aliasloader->removeAlias('ResolvableFour', Foo::class);
        $this->assertFalse($aliasloader->load('ResolvableFour'));
    }

    public function testRemovePatternloadr(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            'PatternResolvable'      => Foo::class,
            'PatternResolvableTwo'   => Foo::class,
            'PatternResolvableThree' => Foo::class,
            'PatternResolvableFour'  => Foo::class,
        ]);
        $this->assertTrue($aliasloader->load('PatternResolvable'));

        $aliasloader->removeAliasPattern('PatternResolvableTwo');
        $this->assertFalse($aliasloader->load('PatternResolvableTwo'));

        $aliasloader->removeAliasPattern('PatternResolvableThree');
        $this->assertFalse($aliasloader->load('PatternResolvableThree'));

        $aliasloader->removeAliasPattern('PatternResolvableFour', Foo::class);
        $this->assertFalse($aliasloader->load('PatternResolvableFour'));
    }

    public function testloadAutoloader(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias([
            'Autoloaded\Foo'        => Foo::class,
            'Second\Autoloaded\Foo' => Foo::class,
            'Third\Autoloaded\Foo'  => Foo::class,
        ]);
        $this->assertFalse(\class_exists('Autoloaded\Foo', true));
        $this->assertTrue($aliasloader->load('Autoloaded\Foo'));

        $aliasloader->register();
        $this->assertTrue(\class_exists('Second\Autoloaded\Foo', true));
        $this->assertTrue($aliasloader->isRegistered());

        $aliasloader->unregister();
        $this->assertFalse(\class_exists('Third\Autoloaded\Foo', true));
        $this->assertFalse($aliasloader->isRegistered());
    }

    public function testStopRecursion(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            '*\*' => '$2\\$1',
        ]);
        $aliasloader->aliasPattern('*', '$1');
        $aliasloader->register();

        $this->assertFalse($aliasloader->load('Unre\Solvable'));
        $this->assertFalse($aliasloader->load('Unresolvable'));
    }

    public function testNamespaceAliasing(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasNamespace('Viserio\\Component\\StaticalProxy\\Tests\\Fixture', '');
        $aliasloader->aliasNamespace('Viserio\\Component\\StaticalProxy\\Tests\\Fixture\\Other', 'Some\\Other\\Space');
        $aliasloader->aliasNamespace('Some\\Space', '');
        $aliasloader->removeNamespaceAlias('Some\\Space');

        $this->assertTrue($aliasloader->load('Foo'));
        $this->assertTrue($aliasloader->load('Some\\Other\\Space\\OtherNameSpace'));
        $this->assertFalse($aliasloader->load('OtherFoo'));
    }

    public function testSetAndGetCachePath(): void
    {
        $path = __DIR__ . '/cache';

        $aliasloader = new AliasLoader();
        $aliasloader->setCachePath($path);

        $this->assertSame($path, $aliasloader->getCachePath());
    }

    public function testGetCachePathThrowExceptionIfRealTimeProxyIsActive(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Please provide a valid cache path.');

        $aliasloader = new AliasLoader();
        $aliasloader->enableRealTimeStaticalProxy();

        $aliasloader->getCachePath();
    }

    public function testRealTimeStaticalProxy(): void
    {
        $path = __DIR__ . '/cache';

        \mkdir($path);

        StaticalProxy::clearResolvedInstances();

        $aliasloader = new AliasLoader();
        $aliasloader->setCachePath($path);
        $aliasloader->enableRealTimeStaticalProxy();

        $class = 'StaticalProxy\\' . Foo::class;

        $aliasloader->load($class);

        $this->assertSame(StaticalProxy::class, \get_parent_class($class));

        $aliasloader->setStaticalProxyNamespace('StaticalProxyTwo\\');

        $class = 'StaticalProxyTwo\\' . Foo::class;

        $aliasloader->load($class);

        $this->assertSame(StaticalProxy::class, \get_parent_class($class));

        (new Filesystem())->remove($path);
    }
}
