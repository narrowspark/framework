<?php
namespace Viserio\StaticalProxy\Tests;

use Viserio\StaticalProxy\AliasLoader;
use Viserio\StaticalProxy\Tests\Fixture\Foo;

class AliasLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLiteral()
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('Test', Foo::class);

        $this->assertTrue($aliasloader->load('Test'));
        $this->assertFalse($aliasloader->load('Unknown'));
    }

    public function testMatchedLiteral()
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern(array(
            'Tester\*' => Foo::class,
        ));

        $this->assertTrue($aliasloader->load('Tester\ThisClass'));
        $this->assertFalse($aliasloader->load('Unknown\ThisClass'));
    }

    public function testMatchedReplacement()
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern(array(
            'Test\*' => 'Viserio\StaticalProxy\Tests\Fixture\$1',
        ));

        $this->assertTrue($aliasloader->load('Test\Foo'));
        $this->assertFalse($aliasloader->load('Test\Unknown'));
    }

    public function testNonExistingResolving()
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('ThisClass', 'ToSomethingThatDoesntExist');

        $this->assertFalse($aliasloader->load('ThisClass'));
    }

    public function testAliasContainingTarget()
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias('FakeFoo::class', Foo::class);

        $this->assertTrue($aliasloader->load('FakeFoo::class'));
    }

    public function testRemoveloadr()
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias([
            'Resolvable'      => Foo::class,
            'ResolvableTwo'   => Foo::class,
            'ResolvableThree' => Foo::class,
            'ResolvableFour'  => Foo::class,
        ]);
        $this->assertTrue($aliasloader->load('Resolvable'));

        $aliasloader->removeAlias('ResolvableTwo');
        $this->assertFalse($aliasloader->load('ResolvableTwo'));

        $aliasloader->removeAlias('ResolvableThree');
        $this->assertFalse($aliasloader->load('ResolvableThree'));

        $aliasloader->removeAlias('ResolvableFour', Foo::class);
        $this->assertFalse($aliasloader->load('ResolvableFour'));
    }

    public function testRemovePatternloadr()
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

    public function testloadAutoloader()
    {
        $aliasloader = new AliasLoader();
        $aliasloader->alias([
            'Autoloaded\Foo'        => Foo::class,
            'Second\Autoloaded\Foo' => Foo::class,
            'Third\Autoloaded\Foo'  => Foo::class,
        ]);
        $this->assertFalse(class_exists('Autoloaded\Foo', true));
        $this->assertTrue($aliasloader->load('Autoloaded\Foo'));

        $aliasloader->register();
        $this->assertTrue(class_exists('Second\Autoloaded\Foo', true));

        $aliasloader->unregister();
        $this->assertFalse(class_exists('Third\Autoloaded\Foo', true));
    }

    public function testStopRecursion()
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasPattern(array(
            '*\*' => '$2\\$1',
        ));
        $aliasloader->aliasPattern('*', '$1');
        $aliasloader->register();

        $this->assertFalse($aliasloader->load('Unre\Solvable'));
        $this->assertFalse($aliasloader->load('Unresolvable'));
    }

    public function testNamespaceAliasing()
    {
        $aliasloader = new AliasLoader();
        $aliasloader->aliasNamespace('Viserio\\StaticalProxy\\Tests\\Fixture', '');
        $aliasloader->aliasNamespace('Viserio\\StaticalProxy\\Tests\\Fixture\\Other', 'Some\\Other\\Space');
        $aliasloader->aliasNamespace('Some\\Space', '');
        $aliasloader->removeNamespaceAlias('Some\\Space');

        $this->assertTrue($aliasloader->load('Foo'));
        $this->assertTrue($aliasloader->load('Some\\Other\\Space\\OtherNameSpace'));
        $this->assertFalse($aliasloader->load('OtherFoo'));
    }
}
