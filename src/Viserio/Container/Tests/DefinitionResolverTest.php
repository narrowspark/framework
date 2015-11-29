<?php
namespace Viserio\Container\Tests;

use Viserio\Container\DefinitionTypes\AliasDefinition;
use Viserio\Container\Container;
use Viserio\Container\DefinitionResolver;
use Viserio\Container\DefinitionTypes\FactoryCallDefinition;
use Viserio\Container\DefinitionTypes\ObjectDefinition;
use Viserio\Container\DefinitionTypes\ParameterDefinition;
use Viserio\Container\DefinitionTypes\Reference;
use Viserio\Container\Tests\Fixture\TestClass;

class DefinitionResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolvesParameterDefinitions()
    {
        $resolver = new DefinitionResolver(new Container());

        $this->assertSame('bar', $resolver->resolve(new ParameterDefinition('foo', 'bar')));
    }

    public function testResolvesInstanceDefinitions()
    {
        $definition = new ObjectDefinition('foo', 'Viserio\Container\Tests\Fixture\TestClass');
        $definition->addPropertyAssignment('publicField', 'public field');
        $definition->addConstructorArgument('constructor param1');
        $definition->addConstructorArgument('constructor param2');
        $definition->addMethodCall('setSomething', 'setter param1', 'setter param2');

        $resolver = new DefinitionResolver(new Container());

        /** @var TestClass $service */
        $service = $resolver->resolve($definition);
        $this->assertInstanceOf('Viserio\Container\Tests\Fixture\TestClass', $service);
        $this->assertSame('public field', $service->publicField);
        $this->assertSame('constructor param1', $service->constructorParam1);
        $this->assertSame('constructor param2', $service->constructorParam2);
        $this->assertSame('setter param1', $service->setterParam1);
        $this->assertSame('setter param2', $service->setterParam2);
    }

    public function testResolvesReferencesInInstanceDefinitions()
    {
        $definition = new ObjectDefinition('foo', 'Viserio\Container\Tests\Fixture\TestClass');
        $definition->addPropertyAssignment('publicField', new Reference('ref1'));
        $definition->addConstructorArgument(new Reference('ref2'));
        $definition->addConstructorArgument(new Reference('ref3'));
        $definition->addMethodCall('setSomething', new Reference('ref4'), new Reference('ref5'));

        $resolver = new DefinitionResolver(new Container([
            'ref1' => 'public field',
            'ref2' => 'constructor param1',
            'ref3' => 'constructor param2',
            'ref4' => 'setter param1',
            'ref5' => 'setter param2',
        ]));

        /** @var TestClass $service */
        $service = $resolver->resolve($definition);
        $this->assertInstanceOf('Viserio\Container\Tests\Fixture\TestClass', $service);
        $this->assertSame('public field', $service->publicField);
        $this->assertSame('constructor param1', $service->constructorParam1);
        $this->assertSame('constructor param2', $service->constructorParam2);
        $this->assertSame('setter param1', $service->setterParam1);
        $this->assertSame('setter param2', $service->setterParam2);
    }

    public function testResolvesAliasDefinitions()
    {
        $resolver = new DefinitionResolver(new Container([
            'bar' => 'qux',
        ]));

        $this->assertSame('qux', $resolver->resolve(new AliasDefinition('foo', 'bar')));
    }

    public function testResolvesServiceFactoryDefinitions()
    {
        $provider = new FakeDefinitionProvider([
            new ObjectDefinition('factory', 'Viserio\Container\Tests\Fixture\FactoryClass'),
        ]);
        $resolver = new DefinitionResolver(new Container([], [$provider]));

        $result = $resolver->resolve(new FactoryCallDefinition('foo', new Reference('factory'), 'create'));

        $this->assertSame('Hello', $result);
    }

    public function testResolvesStaticFactoryDefinitions()
    {
        $resolver = new DefinitionResolver(new Container());

        $definition = new FactoryCallDefinition('foo', 'Viserio\Container\Tests\Fixture\FactoryClass', 'staticCreate');

        $this->assertSame('Hello', $resolver->resolve($definition));
    }

    public function testPassesTheProvidedFactoryArguments()
    {
        $provider = new FakeDefinitionProvider([
            new ObjectDefinition('factory', 'Viserio\Container\Tests\Fixture\FactoryClass'),
            new ParameterDefinition('bar', 'bar'),
        ]);
        $resolver = new DefinitionResolver(new Container([], [$provider]));

        $definition = (new FactoryCallDefinition('foo', new Reference('factory'), 'returnsParameters'))
            ->setArguments('foo', new Reference('bar'));

        $this->assertSame('foobar', $resolver->resolve($definition));
    }
}
