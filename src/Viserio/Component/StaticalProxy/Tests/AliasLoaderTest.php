<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\StaticalProxy\AliasLoader;
use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Component\StaticalProxy\Tests\Fixture\Foo;

class AliasLoaderTest extends TestCase
{
    public function testLiteral(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('TestFoo', Foo::class);

        self::assertTrue($aliasloader->load('TestFoo'));
        self::assertFalse($aliasloader->load('Unknown'));
    }

    public function testMatchedLiteral(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            'Tester\*' => Foo::class,
        ]);

        self::assertTrue($aliasloader->load('Tester\ThisClass'));
        self::assertFalse($aliasloader->load('Unknown\ThisClass'));
    }

    public function testMatchedReplacement(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            'Test\*' => 'Viserio\Component\StaticalProxy\Tests\Fixture\$1',
        ]);

        self::assertTrue($aliasloader->load('Test\Foo'));
        self::assertFalse($aliasloader->load('Test\Unknown'));
    }

    public function testNonExistingResolving(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('ThisClass', 'ToSomethingThatDoesntExist');

        self::assertFalse($aliasloader->load('ThisClass'));
    }

    public function testAliasContainingTarget(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('FakeFoo::class', Foo::class);

        self::assertTrue($aliasloader->load('FakeFoo::class'));
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
        self::assertInternalType('array', $aliasloader->getAliases());
        self::assertTrue($aliasloader->load('Resolvable'));

        $aliasloader->removeAlias('ResolvableTwo');
        self::assertFalse($aliasloader->load('ResolvableTwo'));

        $aliasloader->removeAlias('ResolvableThree');
        self::assertFalse($aliasloader->load('ResolvableThree'));

        $aliasloader->removeAlias('ResolvableFour', Foo::class);
        self::assertFalse($aliasloader->load('ResolvableFour'));
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
        self::assertTrue($aliasloader->load('PatternResolvable'));

        $aliasloader->removeAliasPattern('PatternResolvableTwo');
        self::assertFalse($aliasloader->load('PatternResolvableTwo'));

        $aliasloader->removeAliasPattern('PatternResolvableThree');
        self::assertFalse($aliasloader->load('PatternResolvableThree'));

        $aliasloader->removeAliasPattern('PatternResolvableFour', Foo::class);
        self::assertFalse($aliasloader->load('PatternResolvableFour'));
    }

    public function testloadAutoloader(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias([
            'Autoloaded\Foo'        => Foo::class,
            'Second\Autoloaded\Foo' => Foo::class,
            'Third\Autoloaded\Foo'  => Foo::class,
        ]);
        self::assertFalse(\class_exists('Autoloaded\Foo', true));
        self::assertTrue($aliasloader->load('Autoloaded\Foo'));

        $aliasloader->register();
        self::assertTrue(\class_exists('Second\Autoloaded\Foo', true));
        self::assertTrue($aliasloader->isRegistered());

        $aliasloader->unregister();
        self::assertFalse(\class_exists('Third\Autoloaded\Foo', true));
        self::assertFalse($aliasloader->isRegistered());
    }

    public function testStopRecursion(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern([
            '*\*' => '$2\\$1',
        ]);
        $aliasloader->aliasPattern('*', '$1');
        $aliasloader->register();

        self::assertFalse($aliasloader->load('Unre\Solvable'));
        self::assertFalse($aliasloader->load('Unresolvable'));
    }

    public function testNamespaceAliasing(): void
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasNamespace('Viserio\\Component\\StaticalProxy\\Tests\\Fixture', '');
        $aliasloader->aliasNamespace('Viserio\\Component\\StaticalProxy\\Tests\\Fixture\\Other', 'Some\\Other\\Space');
        $aliasloader->aliasNamespace('Some\\Space', '');
        $aliasloader->removeNamespaceAlias('Some\\Space');

        self::assertTrue($aliasloader->load('Foo'));
        self::assertTrue($aliasloader->load('Some\\Other\\Space\\OtherNameSpace'));
        self::assertFalse($aliasloader->load('OtherFoo'));
    }

    public function testSetAndGetCachePath(): void
    {
        $path = __DIR__ . '/cache';

        $aliasloader = new AliasLoader();
        $aliasloader->setCachePath($path);

        self::assertSame($path, $aliasloader->getCachePath());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Please provide a valid cache path.
     */
    public function testGetCachePathThrowExceptionIfRealTimeProxyIsActive(): void
    {
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

        self::assertSame(StaticalProxy::class, \get_parent_class($class));

        $aliasloader->setStaticalProxyNamespace('StaticalProxyTwo\\');

        $class = 'StaticalProxyTwo\\' . Foo::class;

        $aliasloader->load($class);

        self::assertSame(StaticalProxy::class, \get_parent_class($class));

        (new Filesystem())->remove($path);
    }
}
