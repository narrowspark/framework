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

        static::assertTrue($aliasloader->load('TestFoo'));
        static::assertFalse($aliasloader->load('Unknown'));
    }

    public function testMatchedLiteral(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            'Tester\*' => Foo::class,
        ]);

        static::assertTrue($aliasloader->load('Tester\ThisClass'));
        static::assertFalse($aliasloader->load('Unknown\ThisClass'));
    }

    public function testMatchedReplacement(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            'Test\*' => 'Viserio\Component\StaticalProxy\Tests\Fixture\$1',
        ]);

        static::assertTrue($aliasloader->load('Test\Foo'));
        static::assertFalse($aliasloader->load('Test\Unknown'));
    }

    public function testNonExistingResolving(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('ThisClass', 'ToSomethingThatDoesntExist');

        static::assertFalse($aliasloader->load('ThisClass'));
    }

    public function testAliasContainingTarget(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('FakeFoo::class', Foo::class);

        static::assertTrue($aliasloader->load('FakeFoo::class'));
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
        static::assertInternalType('array', $aliasloader->getAliases());
        static::assertTrue($aliasloader->load('Resolvable'));

        $aliasloader->removeAlias('ResolvableTwo');
        static::assertFalse($aliasloader->load('ResolvableTwo'));

        $aliasloader->removeAlias('ResolvableThree');
        static::assertFalse($aliasloader->load('ResolvableThree'));

        $aliasloader->removeAlias('ResolvableFour', Foo::class);
        static::assertFalse($aliasloader->load('ResolvableFour'));
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
        static::assertTrue($aliasloader->load('PatternResolvable'));

        $aliasloader->removeAliasPattern('PatternResolvableTwo');
        static::assertFalse($aliasloader->load('PatternResolvableTwo'));

        $aliasloader->removeAliasPattern('PatternResolvableThree');
        static::assertFalse($aliasloader->load('PatternResolvableThree'));

        $aliasloader->removeAliasPattern('PatternResolvableFour', Foo::class);
        static::assertFalse($aliasloader->load('PatternResolvableFour'));
    }

    public function testloadAutoloader(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias([
            'Autoloaded\Foo'        => Foo::class,
            'Second\Autoloaded\Foo' => Foo::class,
            'Third\Autoloaded\Foo'  => Foo::class,
        ]);
        static::assertFalse(\class_exists('Autoloaded\Foo', true));
        static::assertTrue($aliasloader->load('Autoloaded\Foo'));

        $aliasloader->register();
        static::assertTrue(\class_exists('Second\Autoloaded\Foo', true));
        static::assertTrue($aliasloader->isRegistered());

        $aliasloader->unregister();
        static::assertFalse(\class_exists('Third\Autoloaded\Foo', true));
        static::assertFalse($aliasloader->isRegistered());
    }

    public function testStopRecursion(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            '*\*' => '$2\\$1',
        ]);
        $aliasloader->aliasPattern('*', '$1');
        $aliasloader->register();

        static::assertFalse($aliasloader->load('Unre\Solvable'));
        static::assertFalse($aliasloader->load('Unresolvable'));
    }

    public function testNamespaceAliasing(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasNamespace('Viserio\\Component\\StaticalProxy\\Tests\\Fixture', '');
        $aliasloader->aliasNamespace('Viserio\\Component\\StaticalProxy\\Tests\\Fixture\\Other', 'Some\\Other\\Space');
        $aliasloader->aliasNamespace('Some\\Space', '');
        $aliasloader->removeNamespaceAlias('Some\\Space');

        static::assertTrue($aliasloader->load('Foo'));
        static::assertTrue($aliasloader->load('Some\\Other\\Space\\OtherNameSpace'));
        static::assertFalse($aliasloader->load('OtherFoo'));
    }

    public function testSetAndGetCachePath(): void
    {
        $path = __DIR__ . '/cache';

        $aliasloader = new AliasLoader();
        $aliasloader->setCachePath($path);

        static::assertSame($path, $aliasloader->getCachePath());
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

        static::assertSame(StaticalProxy::class, \get_parent_class($class));

        $aliasloader->setStaticalProxyNamespace('StaticalProxyTwo\\');

        $class = 'StaticalProxyTwo\\' . Foo::class;

        $aliasloader->load($class);

        static::assertSame(StaticalProxy::class, \get_parent_class($class));

        (new Filesystem())->remove($path);
    }
}
