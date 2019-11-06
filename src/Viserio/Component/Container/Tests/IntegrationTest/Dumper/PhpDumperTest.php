<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Container\Tests\IntegrationTest\Dumper;

use ArrayObject;
use EmptyIterator;
use Exception;
use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use stdClass;
use Viserio\Component\Container\Argument\ClosureArgument;
use Viserio\Component\Container\Argument\ConditionArgument;
use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ConditionDefinition;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Dumper\PhpDumper;
use Viserio\Component\Container\LazyProxy\ProxyDumper;
use Viserio\Component\Container\PhpParser\PrettyPrinter;
use Viserio\Component\Container\RewindableGenerator;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\Container\Tests\Fixture\Autowire\CollisionInterface;
use Viserio\Component\Container\Tests\Fixture\Circular\BarCircular;
use Viserio\Component\Container\Tests\Fixture\Circular\DummyFoobarCircular;
use Viserio\Component\Container\Tests\Fixture\Circular\FoobarCircular;
use Viserio\Component\Container\Tests\Fixture\Circular\FooCircular;
use Viserio\Component\Container\Tests\Fixture\Circular\FooForCircularWithAddCalls;
use Viserio\Component\Container\Tests\Fixture\CustomParentContainer;
use Viserio\Component\Container\Tests\Fixture\EmptyClass;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Container\Tests\Fixture\File\Bar;
use Viserio\Component\Container\Tests\Fixture\File\BarClass;
use Viserio\Component\Container\Tests\Fixture\File\Baz;
use Viserio\Component\Container\Tests\Fixture\File\BazClass;
use Viserio\Component\Container\Tests\Fixture\File\Foo;
use Viserio\Component\Container\Tests\Fixture\File\LazyContext;
use Viserio\Component\Container\Tests\Fixture\FooClass;
use Viserio\Component\Container\Tests\Fixture\FooLazyClass;
use Viserio\Component\Container\Tests\Fixture\Invoke\InvokeCallableClass;
use Viserio\Component\Container\Tests\Fixture\Invoke\InvokeParameterAndConstructorParameterClass;
use Viserio\Component\Container\Tests\Fixture\Invoke\InvokeWithConstructorParameterClass;
use Viserio\Component\Container\Tests\Fixture\Make\ContractFixtureInterface;
use Viserio\Component\Container\Tests\Fixture\Method\CallFixture;
use Viserio\Component\Container\Tests\Fixture\Preload\C1;
use Viserio\Component\Container\Tests\Fixture\Preload\C2;
use Viserio\Component\Container\Tests\Fixture\Preload\C3;
use Viserio\Component\Container\Tests\Fixture\ScalarFactory;
use Viserio\Component\Container\Tests\Fixture\Wither;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Exception\LogicException;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;

/**
 * @internal
 *
 * @small
 */
final class PhpDumperTest extends AbstractContainerTestCase
{
    public const TEST = 'TEST';

    protected const DUMP_CLASS_CONTAINER = false;

    protected const SKIP_TEST_PIPE = true;

    public function testCompilingToAnInvalidClassNameThrowsAnError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The container cannot be compiled: [123-abc] is not a valid PHP class name.');

        $this->containerBuilder->compile();

        $dumper = new PhpDumper($this->containerBuilder);
        $dumper->dump(['class' => '123-abc']);
    }

    public function testContainerCanBeDumpedWithExtendCustomClass(): void
    {
        $this->containerBuilder->compile();

        $this->dumperOptions = [
            'base_class' => CustomParentContainer::class,
        ];

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithNamespace(): void
    {
        $this->containerBuilder->compile();

        $className = $this->getDumperContainerClassName(\ucfirst(__FUNCTION__));
        $dirPath = \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;

        self::assertStringEqualsFile(
            $dirPath . $className . '.php',
            $this->getPhpDumper()->dump(['class' => $className, 'namespace' => 'Viserio\Component\Container\Test\Fixture\Compiled'])
        );
    }

    public function testPhpDumperCantBeCalledWithoutACompiledContainer(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot dump an uncompiled container.');

        new PhpDumper($this->containerBuilder);
    }

    public function testContainerCanBeDumpedWithOptimizedString(): void
    {
        $this->containerBuilder->bind('test', (object) [
            'foo' => 'test',
            'only dot' => '.',
            'concatenation as value' => '.\'\'.',
            'concatenation from the start value' => '\'\'.',
            '.' => 'dot as a key',
            '.\'\'.' => 'concatenation as a key',
            '\'\'.' => 'concatenation from the start key',
            'optimize concatenation' => 'string1{some_string}string2',
            'optimize concatenation with empty string' => 'string1{empty_value}string2',
            'optimize concatenation from the start' => '{empty_value}start',
            'optimize concatenation at the end' => 'end{empty_value}',
            'new line' => "string with \nnew line",
        ])->setPublic(true);

        $this->containerBuilder->setParameter('empty_value', '');
        $this->containerBuilder->setParameter('some_string', '-');
        $this->containerBuilder->compile();

        /** @var \Viserio\Component\Container\Definition\ObjectDefinition $definition */
        $definition = $this->containerBuilder->getDefinition('test');

        $this->assertDumpedContainer(__FUNCTION__);

        $object = $this->container->get('test');

        self::assertInstanceOf(stdClass::class, $object);
        self::assertCount(\count($definition->getProperties()), (array) $object);
        // parameter are not removed
        self::assertSame('', $this->container->getParameter('empty_value'));
        self::assertSame('-', $this->container->getParameter('some_string'));
    }

    public function testContainerCanBeDumpedWithParameters(): void
    {
        $data = [
            'null' => null,
            'true' => true,
            'false' => false,
            'int1' => 1,
            'int0' => 0,
            'float' => 31.10,
            'empty' => '',
            'Foo' => 'bar',
            'BAR' => 'foo',
            'foo' => '{Foo}',
            'baz' => 'foo is {}foo baz',
            'escape' => '@escapeme',
            'binary' => "\xf0\xf0\xf0\xf0",
            'binary-control-char' => "This is a Bell char \x07",
            'true2' => 'true',
            'false2' => 'false',
            'null2' => 'null',
        ];

        foreach ($data as $key => $value) {
            $this->containerBuilder->setParameter($key, $value);
        }

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        foreach ($data as $key => $value) {
            if ($value === '{Foo}') {
                $value = 'bar';
            }

            self::assertSame($value, $this->container->getParameter($key));
        }
    }

    public function testScalarService(): void
    {
        $this->containerBuilder->bind('foo', [ScalarFactory::class, 'getSomeValue'])
            ->setPublic(true)
            ->setReturnType('string');

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertTrue($this->container->has('foo'));
        self::assertSame('some value', $this->container->get('foo'));
    }

    public function testContainerCanBeDumpedWithExtendedParameters(): void
    {
        $this->containerBuilder->setParameter('foo', 'bar');
        $this->containerBuilder->setParameter('be', 'be_');
        $this->containerBuilder->extend('foo', static function (DefinitionContract $definition) {
            return $definition->setValue('DIFFERENT_' . $definition->getValue());
        });
        $this->containerBuilder->extend('foo', static function (DefinitionContract $definition, ContainerBuilder $container) {
            return $definition->setValue($container->getParameter('be')->getValue() . $definition->getValue());
        });

        $this->containerBuilder->compile();
        $className = \ucfirst(__FUNCTION__);

        $this->dumpContainer($className);

        self::assertSame('be_DIFFERENT_bar', $this->container->getParameter('foo'));
        self::assertSame('be_', $this->container->getParameter('be'));
    }

    public function testContainerCanBeDumpedWithArray(): void
    {
        $data = [
            'foo' => 'bar',
            'null' => null,
            'true' => true,
            'false' => false,
            'int1' => 1,
            'int0' => 0,
            'float' => 31.10,
            'empty' => '',
        ];

        $this->containerBuilder->bind('foo', $data)->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        $array = $this->container->get('foo');

        self::assertCount(\count($data), $array);
        self::assertSame($data, $array);
    }

    public function testContainerCanBeDumpedWithComplicatedArray(): void
    {
        $expectedArray = [
            1 => 'int_key',
            'string' => 'string',
            'int' => 0,
            'float' => 1.1,
            'bool' => false,
            'array' => [
                1 => 'int_key',
                'string' => 'string',
                'int' => 0,
                'float' => 1.1,
                'bool' => false,
                'object' => new stdClass(),
                'null' => null,
                'anoObject' => new class() {
                    /** @var string */
                    public $test = 'test';
                },
                Exception::class => Exception::class,
            ],
            'object' => new stdClass(),
            'null' => null,
            'anoObject' => new class() {
                /** @var string */
                public $test = 'test2';
            },
            'object_with_properties' => (object) [
                'only dot' => '.',
            ],
            Exception::class => Exception::class,
        ];

        $this->containerBuilder->bind(
            'foo',
            $expectedArray
        )->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);

        $array = $this->container->get('foo');

        self::assertCount(\count($expectedArray), $array);

        foreach ($expectedArray as $key => $value) {
            $this->assertArrayContent($array, $key, $value);
        }
    }

    public function testContainerCanBeDumpedWithExtendArray(): void
    {
        $this->containerBuilder->bind('foo', ['foo' => 'bar'])
            ->setPublic(true);
        $this->containerBuilder->bind('be', ['bar' => 'hey'])
            ->setPublic(true);
        $this->containerBuilder->extend('foo', function (DefinitionContract $definition, ContainerBuilder $container) {
            return $definition->setValue(\array_merge($container->getDefinition('be')->getValue(), $definition->getValue()));
        });
        $this->containerBuilder->extend('be', function (DefinitionContract $definition) {
            $array = $definition->getValue();

            $array['test'] = 'yeah';

            return $definition->setValue($array);
        });

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame(['bar' => 'hey', 'foo' => 'bar'], $this->container->get('foo'));
        self::assertSame(['bar' => 'hey', 'test' => 'yeah'], $this->container->get('be'));
    }

    public function testContainerCanBeDumpedWithSimpleClosure(): void
    {
        $this->containerBuilder->bind('closure', function () {
            return 'test';
        })
            ->setExecutable(true)
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame('test', $this->container->get('closure'));
    }

    public function testContainerCanBeDumpedWithClosureAndParameters(): void
    {
        $this->containerBuilder->bind(stdClass::class);
        $this->containerBuilder->bind('closure', static function (ContainerInterface $container, stdClass $stdClass) {
            return $container;
        })
            ->setExecutable(true)
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithEmptyAnonymousClass(): void
    {
        $this->containerBuilder->singleton('ano', new class() {
        })->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithAnonymousClassAndInterfaces(): void
    {
        $this->containerBuilder->singleton('ano', new class() implements ContractFixtureInterface {
        })->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithAnonymousClassAndDefaultConstructor(): void
    {
        $this->containerBuilder->singleton('ano', new class() {
            /** @var null|string */
            private $text;

            public function __construct(?string $text = 'test')
            {
                $this->text = $text;
            }

            /**
             * @return null|string
             */
            public function getText(): ?string
            {
                return $this->text;
            }
        })->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);

        $anoObject = $this->container->get('ano');

        self::assertSame('test', $anoObject->getText());
    }

    public function testContainerCanBeDumpedWithAnonymousClassAndConstructor(): void
    {
        $class = new class('test') {
            /** @var string */
            private $text;

            public function __construct(string $text)
            {
                $this->text = $text;
            }

            /**
             * @return string
             */
            public function getText(): string
            {
                return $this->text;
            }
        };

        $this->containerBuilder->singleton('ano', $class)->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithAnonymousClassAndTrait(): void
    {
        $this->containerBuilder->bind('foo', new class() {
            use ContainerAwareTrait;

            public function add(ContainerInterface $container): void
            {
                $this->container = $container;
            }
        })->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testCompiledContainerIsIdempotent(): void
    {
        $containerBuilder1 = new ContainerBuilder();
        $this->arrangeContainerEntries($containerBuilder1);

        $containerBuilder1->compile();

        $dumper1 = new PhpDumper($containerBuilder1);
        $className1 = \ucfirst(__FUNCTION__) . '1';

        $containerBuilder2 = new ContainerBuilder();
        $this->arrangeContainerEntries($containerBuilder2);

        $containerBuilder2->compile();

        $dumper2 = new PhpDumper($containerBuilder2);
        $className2 = \ucfirst(__FUNCTION__) . '1';

        // The method mapping of the resulting CompiledContainers should be equal
        self::assertEquals(
            $dumper1->dump(['class' => $className1]),
            $dumper2->dump(['class' => $className2])
        );
    }

    public function testContainerCanBeDumpedWithUseClassInCallable(): void
    {
        $this->containerBuilder->bind('be', function () {
            return new FactoryClass();
        })
            ->setExecutable(true)
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithAutowiredMethodCall(): void
    {
        $this->containerBuilder->bind('foo', new class() {
            public function add(ContainerInterface $container): void
            {
            }
        })
            ->addMethodCall('add')
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithMethodCallsThatReferencingContainer(): void
    {
        $this->containerBuilder->bind('foo', new class() {
            public function add(ContainerInterface $container): void
            {
            }
        })
            ->addMethodCall('add', [
                (new ReferenceDefinition(ContainerInterface::class))
                    ->setVariableName('this'),
            ])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithMethodCallAndDefaultValue(): void
    {
        $this->containerBuilder->bind('foo', new class() {
            public function add(string $container = 'test'): void
            {
            }
        })
            ->addMethodCall('add')
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithMethodCallAndOverwrittenDefaultValue(): void
    {
        $this->containerBuilder->bind('foo', new class() {
            public function add(string $container = 'test'): void
            {
            }
        })
            ->addMethodCall('add', ['test2'])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithMethodCallAndClosureValue(): void
    {
        $this->containerBuilder->bind('foo', new class() {
            public function add(callable $container): void
            {
            }
        })
            ->addMethodCall('add', [static function (): void {
            }])
            ->addMethodCall('add', [function () {
                return 'test';
            }])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithEmptySdtClass(): void
    {
        $this->containerBuilder->bind(stdClass::class)->setPublic(true);
        $this->containerBuilder->bind('foo', stdClass::class)->setPublic(true);
        $this->containerBuilder->singleton('bar', new stdClass())->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertInstanceOf(stdClass::class, $this->container->get(stdClass::class));
        self::assertInstanceOf(stdClass::class, $this->container->get('foo'));
        self::assertInstanceOf(stdClass::class, $this->container->get('bar'));
    }

    public function testContainerCanBeDumpedWithSdtClassAndProperties(): void
    {
        $class = new stdClass();
        $class->bar = true;

        $this->containerBuilder->bind('bar', $class)->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertTrue($this->container->get('bar')->bar);
    }

    /**
     * @group legacy
     * @expectedDeprecation The [alias_for_foo_deprecated] service alias is deprecated. You should stop using it, as it will be removed in the future.
     */
    public function testContainerCanBeDumpedWithAliasesDeprecation(): void
    {
        $this->containerBuilder->bind('foo', stdClass::class)
            ->setPublic(true);
        $this->containerBuilder->setAlias('foo', 'alias_for_foo_deprecated')
            ->setPublic(true)
            ->setDeprecated();
        $this->containerBuilder->setAlias('foo', 'alias_for_foo_non_deprecated')
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer($className = \ucfirst(__FUNCTION__));

        $this->container->get('alias_for_foo_deprecated');
    }

    /**
     * @group legacy
     * @expectedDeprecation Deprecated [alias_for_foo_deprecated].
     */
    public function testContainerCanBeDumpedWithAliasesDeprecationAndCustomMessage(): void
    {
        $this->containerBuilder->bind('foo', stdClass::class)
            ->setPublic(true);
        $this->containerBuilder->setAlias('foo', 'alias_for_foo_deprecated')
            ->setPublic(true)
            ->setDeprecated(true, 'Deprecated [%s].');
        $this->containerBuilder->setAlias('foo', 'alias_for_foo_non_deprecated')
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer($className = \ucfirst(__FUNCTION__));

        $this->container->get('alias_for_foo_deprecated');
    }

    public function testContainerCanBeDumpedWithOneClassAndManyInlineClasses(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $this->containerBuilder->bind("Viserio\\Component\\Container\\Tests\\Fixture\\Inline\\Class{$i}");
        }
        $this->containerBuilder->getDefinition('Viserio\\Component\\Container\\Tests\\Fixture\\Inline\\Class20')->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithWither(): void
    {
        $this->containerBuilder
            ->singleton('wither', Wither::class)
            ->addMethodCall('withEmptyClass1', [new ReferenceDefinition(EmptyClass::class)], true)
            ->addMethodCall('withEmptyClass2', [new ReferenceDefinition(EmptyClass::class)], true)
            ->addMethodCall('setEmptyClass', [new ReferenceDefinition(EmptyClass::class)])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertInstanceOf(EmptyClass::class, $this->container->get('wither')->foo);
    }

    public function testContainerCanBeDumpedWithClassNameInFactory(): void
    {
        $this->containerBuilder->singleton(FactoryClass::class)
            ->setPublic(true);
        $this->containerBuilder->bind('foo', [FactoryClass::class, 'create'])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame('Hello', $this->container->get('foo'));
    }

    public function testContainerCanBeDumpedWithReferenceInFactory(): void
    {
        $this->containerBuilder->singleton(FactoryClass::class)
            ->setPublic(true);
        $this->containerBuilder->bind('foo', [new ReferenceDefinition(FactoryClass::class), 'create'])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame('Hello', $this->container->get('foo'));
    }

    public function testContainerCanBeDumpedWithObjectAsFactoryCall(): void
    {
        $this->containerBuilder->bind('foo', [new FactoryClass(), 'create'])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame('Hello', $this->container->get('foo'));
    }

    public function testContainerCanBeDumpedWithClassAndStaticMethodInFactory(): void
    {
        $this->containerBuilder->bind('foo', [FactoryClass::class, 'staticCreate'])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame('Hello', $this->container->get('foo'));
    }

    public function testContainerCanBeDumpedWithArrayObject(): void
    {
        $fruits = [
            'apple' => 'yummy',
            'orange' => 'ah ya, nice',
            'grape' => 'wow, I love it!',
            'plum' => 'nah, not me',
        ];
        $this->containerBuilder->singleton('foo', new ArrayObject($fruits))
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame($fruits, \iterator_to_array($this->container->get('foo')));
    }

    public function testContainerCanBeDumpedWithEmptyIterator(): void
    {
        $this->containerBuilder->singleton('foo', new EmptyIterator())
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithInvokeCallableClass(): void
    {
        $this->containerBuilder->singleton('foo', [InvokeCallableClass::class, '__invoke'])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame(42, $this->container->get('foo'));
    }

    public function testContainerCanBeDumpedWithInvokeWithConstructorParameterClass(): void
    {
        $this->containerBuilder->singleton('foo', [InvokeWithConstructorParameterClass::class, '__invoke'])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame('hallo', $this->container->get('foo'));
    }

    public function testContainerCanBeDumpedWithInvokeParameterAndConstructorParameterClass(): void
    {
        $this->containerBuilder->singleton('foo', [InvokeParameterAndConstructorParameterClass::class, '__invoke'])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertInstanceOf(InvokeCallableClass::class, $this->container->get('foo'));
    }

    public function testContainerCanBeDumpedWithUninitializedReferences(): void
    {
        $this->containerBuilder->singleton('foo1', stdClass::class)
            ->setPublic(true);
        $this->containerBuilder->singleton('foo2', stdClass::class)
            ->setPublic(false);
        $this->containerBuilder->singleton('foo3', stdClass::class)
            ->setPublic(false);
        $this->containerBuilder->singleton('baz', stdClass::class)
            ->setProperty('foo3', new ReferenceDefinition('foo3'))
            ->setPublic(true);

        $this->containerBuilder
            ->singleton('bar', stdClass::class)
            ->setProperty('foo1', new ReferenceDefinition('foo1', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE))
            ->setProperty('foo2', new ReferenceDefinition('foo2', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE))
            ->setProperty('foo3', new ReferenceDefinition('foo3', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE))
            ->setProperty('closures', [
                new ClosureArgument(new ReferenceDefinition('foo1', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)),
                new ClosureArgument(new ReferenceDefinition('foo2', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)),
                new ClosureArgument(new ReferenceDefinition('foo3', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)),
            ])
            ->setProperty('iter', new IteratorArgument([
                'foo1' => new ReferenceDefinition('foo1', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE),
                'foo2' => new ReferenceDefinition('foo2', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE),
                'foo3' => new ReferenceDefinition('foo3', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE),
            ]))
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        $bar = $this->container->get('bar');

        self::assertNull($bar->foo1);
        self::assertNull($bar->foo2);
        self::assertNull($bar->foo3);
        self::assertNull($bar->closures[0]());
        self::assertNull($bar->closures[1]());
        self::assertNull($bar->closures[2]());
        self::assertSame([], \iterator_to_array($bar->iter));

        $fullClassName = $this->getNamespace() . '\\' . $this->getDumperContainerClassName(__FUNCTION__);
        $this->container = new $fullClassName();

        $this->container->get('foo1');
        $this->container->get('baz');

        $bar = $this->container->get('bar');

        self::assertEquals(new stdClass(), $bar->foo1);
        self::assertNull($bar->foo2);
        self::assertEquals(new stdClass(), $bar->foo3);
        self::assertEquals(new stdClass(), $bar->closures[0]());
        self::assertNull($bar->closures[1]());
        self::assertEquals(new stdClass(), $bar->closures[2]());
        self::assertEquals(['foo1' => new stdClass(), 'foo3' => new stdClass()], \iterator_to_array($bar->iter));
    }

    public function testContainerCanBeDumpedWithIgnoreOnInvalidReference(): void
    {
        $this->containerBuilder->bind('maaa', FactoryClass::class)
            ->addMethodCall('add', [new ReferenceDefinition(CollisionInterface::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $logs = $this->containerBuilder->getLogs();

        self::assertSame('Viserio\Component\Container\Pipeline\ResolveInvalidReferencesPipe: Removed invalid reference for [Viserio\Component\Container\Tests\Fixture\Autowire\CollisionInterface].', $logs[0]);
        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithMethodCallsInReferenceDefinition(): void
    {
        $this->containerBuilder->bind('bar', FactoryClass::class)
            ->addMethodCall('returnsParameters', [
                (new ReferenceDefinition(FactoryClass::class))->addMethodCall('create'),
                (new ReferenceDefinition(FactoryClass::class))->setVariableName('da')->addMethodCall('create'),
            ])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithMethodCallsInSelfReferenceDefinition(): void
    {
        $this->containerBuilder->singleton('bar', FactoryClass::class)
            ->addMethodCall('returnsParameters', [
                (new ReferenceDefinition('bar'))->addMethodCall('create'),
                (new ReferenceDefinition('bar'))->addMethodCall('create'),
            ])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithFactoryAndProperties(): void
    {
        $this->containerBuilder->singleton('foo', [FactoryClass::class, 'createFooClass'])
            ->setProperties(['foo' => 'bar', 'moo' => 'foo'])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedWithFactoryAndMethodCall(): void
    {
        $this->containerBuilder->singleton('foo', [FactoryClass::class, 'createFooClass'])
            ->addMethodCall('setBar')
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testContainerCanBeDumpedAsFiles(): void
    {
        $this->arrangeFilesContainerConfiguration();

        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', true);
        $this->containerBuilder->setParameter('container.dumper.as_files', true);
        $this->containerBuilder->compile();

        $className = $this->getDumperContainerClassName(__FUNCTION__);
        $dirPath = \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
        $namespace = $this->getNamespace();

        $this->arrangePhpParser();

        $content = $this->getPhpDumper()->dump([
            'class' => $className,
            'namespace' => $namespace,
            'file' => $dirPath,
        ]);

        self::assertStringMatchesFormatFile($dirPath . $className . '.txt', \print_r($content, true));
    }

    public function testPreloadOptimizations(): void
    {
        $this->containerBuilder->singleton(C1::class)
            ->addTag('container.preload')
            ->setPublic(true);
        $this->containerBuilder->singleton(C2::class)
            ->addArgument(new ReferenceDefinition(C3::class))
            ->setPublic(true);
        $this->containerBuilder->singleton(C3::class);

        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', true);

        $this->containerBuilder->compile();

        $this->dumperOptions = [
            'file' => \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . 'Preload' . \DIRECTORY_SEPARATOR,
        ];

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testDumpRelativeDir(): void
    {
        $this->containerBuilder->singleton(stdClass::class)
            ->addArgument('{foo}')
            ->addArgument(['{foo}' => '{buz}/'])
            ->setPublic(true);

        $this->containerBuilder->setParameter('foo', 'wiz' . \dirname(__DIR__));
        $this->containerBuilder->setParameter('bar', __DIR__);
        $this->containerBuilder->setParameter('baz', '{bar}/PhpDumperTest.php');
        $this->containerBuilder->setParameter('buz', \dirname(__DIR__, 2));

        $this->containerBuilder->compile();

        $this->dumperOptions = [
            'file' => __FILE__,
        ];

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testDumpAsFilesWithFactoriesInlined(): void
    {
        $this->arrangeFilesContainerConfiguration();

        $this->containerBuilder->setParameter('container.dumper.inline_factories', true);
        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', true);

        /** @var \Viserio\Contract\Container\Definition\TagAwareDefinition $definition */
        $definition = $this->containerBuilder->getDefinition('bar');
        $definition->addTag('preload');

        $this->containerBuilder->bind('non_shared_foo', FooClass::class)
            ->setPublic(true);
        $this->containerBuilder->setParameter('container.dumper.as_files', true);

        $this->containerBuilder->compile();

        $className = $this->getDumperContainerClassName(__FUNCTION__);
        $dirPath = \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;

        $this->arrangePhpParser();

        $content = $this->getPhpDumper()->dump([
            'class' => $className,
            'file' => $dirPath,
        ]);

        self::assertStringMatchesFormatFile($dirPath . $className . '.txt', \print_r($content, true));
    }

    public function testDumpAsFilesWithLazyFactoriesInlined(): void
    {
        $this->containerBuilder->setParameter('container.dumper.inline_factories', true);
        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', true);

        $this->containerBuilder->singleton('lazy_foo', FooClass::class)
            ->addArgument(new ObjectDefinition(FooLazyClass::class, FooLazyClass::class, 1))
            ->setPublic(true)
            ->setLazy(true);
        $this->containerBuilder->setParameter('container.dumper.as_files', true);
        $this->containerBuilder->compile();

        $this->proxyDumper = new ProxyDumper();

        $className = $this->getDumperContainerClassName(__FUNCTION__);
        $dirPath = \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;

        $this->arrangePhpParser();

        $content = $this->getPhpDumper()->dump([
            'class' => $className,
            'file' => $dirPath,
        ]);

        self::assertStringMatchesFormatFile($dirPath . $className . '.txt', \print_r($content, true));
    }

    public function testNonSharedLazyDumpAsFiles(): void
    {
        $this->containerBuilder->bind('non_shared_foo', EmptyClass::class)
            ->setPublic(true)
            ->setLazy(true);

        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', true);
        $this->containerBuilder->setParameter('container.dumper.as_files', true);

        $this->containerBuilder->compile();

        $this->proxyDumper = new ProxyDumper();

        $className = $this->getDumperContainerClassName(__FUNCTION__);
        $dirPath = \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
        $namespace = $this->getNamespace();

        $this->arrangePhpParser();

        $content = $this->getPhpDumper()->dump([
            'class' => $className,
            'namespace' => $namespace,
            'file' => $dirPath,
        ]);

        self::assertStringMatchesFormatFile($dirPath . $className . '.txt', \print_r($content, true));
    }

    public function testNonSharedLazyDumpAsFilesWithFalseInlineClassLoader(): void
    {
        $this->containerBuilder->bind('non_shared_foo', EmptyClass::class)
            ->setPublic(true)
            ->setLazy(true);

        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', false);
        $this->containerBuilder->setParameter('container.dumper.as_files', true);

        $this->containerBuilder->compile();

        $this->proxyDumper = new ProxyDumper();

        $className = $this->getDumperContainerClassName(__FUNCTION__);
        $dirPath = \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
        $namespace = $this->getNamespace();

        $this->arrangePhpParser();

        $content = $this->getPhpDumper()->dump([
            'class' => $className,
            'namespace' => $namespace,
            'file' => $dirPath,
        ]);

        self::assertStringMatchesFormatFile($dirPath . $className . '.txt', \print_r($content, true));
    }

    public function testConflictingServiceIds(): void
    {
        $this->containerBuilder->singleton('foo_bar', stdClass::class)
            ->setPublic(true);
        $this->containerBuilder->singleton('foobar', stdClass::class)
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testOverrideServiceWhenUsingADumpedContainer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The [foo_bar] service is already initialized, you cannot replace it.');

        $this->containerBuilder->singleton('foo_bar', stdClass::class)
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertInstanceOf(stdClass::class, $this->container->get('foo_bar'));

        $this->container->set('foo_bar', $decorator = 'fa');

        self::assertSame($decorator, $this->container->get('foo_bar'), '->set() overrides an already defined service');
    }

    public function testContainerCanBeDumpedWithComplexClosure(): void
    {
        $this->containerBuilder->bind('closure', function () {
            $instance = new stdClass();

            $instance->foo = 'test';
            $instance->{'only dot'} = '.';
            $instance->{'concatenation as value'} = '.\'\'.';
            $instance->{'concatenation from the start value'} = '\'\'.';
            $instance->{'.'} = 'dot as a key';
            $instance->{'.\'\'.'} = 'concatenation as a key';
            $instance->{'\'\'.'} = 'concatenation from the start key';
            $instance->{'optimize concatenation'} = 'string1-string2';
            $instance->{'optimize concatenation with empty string'} = 'string1string2';
            $instance->{'optimize concatenation from the start'} = 'start';
            $instance->{'optimize concatenation at the end'} = 'end';
            $instance->{'new line'} = 'string with ' . "\n"
                . 'new line';
            $instance->self = self::TEST;

            return $instance;
        })
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->arrangePhpParser();

        $this->assertDumpedContainer(__FUNCTION__);

        $closure = ($this->container->get('closure'))();

        self::assertSame(self::TEST, $closure->self);
        self::assertSame('.', $closure->{'only dot'});
    }

    public function testDedupLazyProxy(): void
    {
        $this->containerBuilder->singleton('foo', stdClass::class)
            ->setLazy(true)
            ->setPublic(true);
        $this->containerBuilder->singleton('bar', stdClass::class)
            ->setLazy(true)
            ->setPublic(true);

        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', true);

        $this->containerBuilder->compile();

        $this->proxyDumper = new ProxyDumper();

        $className = $this->getDumperContainerClassName(__FUNCTION__);
        $dirPath = \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
        $namespace = $this->getNamespace();

        $this->arrangePhpParser();

        $content = $this->getPhpDumper()->dump([
            'class' => $className,
            'namespace' => $namespace,
            'file' => $dirPath,
        ]);

        self::assertStringMatchesFormatFile($dirPath . $className . '.txt', \print_r($content, true));
    }

    public function testLazyArgumentProvideGenerator(): void
    {
        $this->containerBuilder->singleton('lazy_referenced', stdClass::class)->setPublic(true);
        $this->containerBuilder
            ->singleton('lazy_context', LazyContext::class)
            ->setPublic(true)
            ->setArguments([
                new IteratorArgument(['k1' => new ReferenceDefinition('lazy_referenced'), 'k2' => new ReferenceDefinition(ContainerInterface::class)]),
                new IteratorArgument([]),
            ]);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        $lazyContext = $this->container->get('lazy_context');

        self::assertInstanceOf(RewindableGenerator::class, $lazyContext->lazyValues);
        self::assertInstanceOf(RewindableGenerator::class, $lazyContext->lazyEmptyValues);
        self::assertCount(2, $lazyContext->lazyValues);
        self::assertCount(0, $lazyContext->lazyEmptyValues);

        $i = -1;

        foreach ($lazyContext->lazyValues as $k => $v) {
            switch (++$i) {
                case 0:
                    self::assertEquals('k1', $k);
                    self::assertInstanceOf('stdCLass', $v);

                    break;
                case 1:
                    self::assertEquals('k2', $k);
                    self::assertInstanceOf(ContainerInterface::class, $v);

                    break;
            }
        }
        self::assertEmpty(\iterator_to_array($lazyContext->lazyEmptyValues));
    }

    public function testPrivateWithIgnoreOnInvalidReference(): void
    {
        $this->containerBuilder->singleton('not_invalid', BazClass::class);
        $this->containerBuilder->singleton('bar', BarClass::class)
            ->setPublic(true)
            ->addMethodCall('setBaz', [new ReferenceDefinition('not_invalid', ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)]);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertInstanceOf(BazClass::class, $this->container->get('bar')->getBaz());
    }

    /**
     * @dataProvider provideAlmostCircularCases
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param mixed $visibility
     */
    public function testAlmostCircular($visibility): void
    {
        $public = 'public' === $visibility;

        // same visibility for deps
        $this->containerBuilder->singleton('foo', FooCircular::class)
            ->setPublic(true)
            ->addArgument(new ReferenceDefinition('bar'));
        $this->containerBuilder->singleton('bar', BarCircular::class)
            ->setPublic($public)
            ->addMethodCall('addFoobar', [new ReferenceDefinition('foobar')]);
        $this->containerBuilder->singleton('foobar', FoobarCircular::class)
            ->setPublic($public)
            ->addArgument(new ReferenceDefinition('foo'));

        // mixed visibility for deps
        $this->containerBuilder->singleton('foo2', FooCircular::class)
            ->setPublic(true)
            ->addArgument(new ReferenceDefinition('bar2'));
        $this->containerBuilder->singleton('bar2', BarCircular::class)
            ->setPublic(! $public)
            ->addMethodCall('addFoobar', [new ReferenceDefinition('foobar2')]);
        $this->containerBuilder->singleton('foobar2', FoobarCircular::class)
            ->setPublic($public)
            ->addArgument(new ReferenceDefinition('foo2'));

        // simple inline setter with internal reference
        $this->containerBuilder->singleton('bar3', BarCircular::class)
            ->setPublic(true)
            ->addMethodCall(
                'addFoobar',
                [new ReferenceDefinition('foobar3'), new ReferenceDefinition('foobar3')]
            );
        $this->containerBuilder->singleton('foobar3', DummyFoobarCircular::class)
            ->setPublic($public);

        // loop with non-shared dep
        $this->containerBuilder->bind('foo4', stdClass::class)
            ->setPublic($public)
            ->setProperty('foobar', new ReferenceDefinition('foobar4'));

        $this->containerBuilder->singleton('foobar4', stdClass::class)
            ->setPublic(true)
            ->addArgument(new ReferenceDefinition('foo4'));

        // loop on the constructor of a setter-injected dep with property
        $this->containerBuilder->singleton('foo5', stdClass::class)
            ->setPublic(true)
            ->setProperty('bar', new ReferenceDefinition('bar5'));

        $this->containerBuilder->singleton('bar5', stdClass::class)
            ->setPublic($public)
            ->addArgument(new ReferenceDefinition('foo5'))
            ->setProperty('foo', new ReferenceDefinition('foo5'));

        // doctrine-like event system + some extra
        $this->containerBuilder->singleton('manager', stdClass::class)
            ->setPublic(true)
            ->addArgument(new ReferenceDefinition('connection'));
        $this->containerBuilder->singleton('logger', stdClass::class)
            ->setPublic(true)
            ->addArgument(new ReferenceDefinition('connection'))
            ->setProperty(
                'handler',
                (new ObjectDefinition(stdClass::class, stdClass::class, 2))
                    ->addArgument(new ReferenceDefinition('manager'))
            );
        $this->containerBuilder->singleton('connection', stdClass::class)
            ->setPublic(true)
            ->addArgument(new ReferenceDefinition('dispatcher'))
            ->addArgument(new ReferenceDefinition('config'));
        $this->containerBuilder->singleton('config', stdClass::class)
            ->setPublic(false)
            ->setProperty('logger', new ReferenceDefinition('logger'));
        $this->containerBuilder->singleton('dispatcher', stdClass::class)
            ->setPublic($public)
            ->setLazy($public)
            ->setProperty('subscriber', new ReferenceDefinition('subscriber'));
        $this->containerBuilder->singleton('subscriber', stdClass::class)
            ->setPublic(true)
            ->addArgument(new ReferenceDefinition('manager'));

        // doctrine-like event system + some extra (bis)
        $this->containerBuilder->singleton('manager2', stdClass::class)
            ->setPublic(true)
            ->addArgument(new ReferenceDefinition('connection2'));
        $this->containerBuilder->singleton('logger2', stdClass::class)
            ->setPublic(false)
            ->addArgument(new ReferenceDefinition('connection2'))
            ->setProperty(
                'handler2',
                (new ObjectDefinition(stdClass::class, stdClass::class, 2))
                    ->addArgument(new ReferenceDefinition('manager2'))
            );
        $this->containerBuilder->singleton('connection2', stdClass::class)
            ->setPublic(true)
            ->addArgument(new ReferenceDefinition('dispatcher2'))
            ->addArgument(new ReferenceDefinition('config2'));
        $this->containerBuilder->singleton('config2', stdClass::class)
            ->setPublic(false)
            ->setProperty('logger2', new ReferenceDefinition('logger2'));
        $this->containerBuilder->singleton('dispatcher2', stdClass::class)
            ->setPublic($public)
            ->setLazy($public)
            ->setProperty('subscriber2', new ReferenceDefinition('subscriber2'));
        $this->containerBuilder->singleton('subscriber2', stdClass::class)
            ->setPublic(false)
            ->addArgument(new ReferenceDefinition('manager2'));

        // doctrine-like event system with listener
        $this->containerBuilder->singleton('manager3', 'stdClass')
            ->setLazy(true)
            ->setPublic(true) // just for assertions
            ->addArgument(new ReferenceDefinition('connection3'));
        $this->containerBuilder->singleton('connection3', 'stdClass')
            ->setPublic($public)
            ->setProperty('listener', [new ReferenceDefinition('listener3')]);
        $this->containerBuilder->singleton('listener3', 'stdClass')
            ->setPublic(true) // just for assertions
            ->setProperty('manager', new ReferenceDefinition('manager3'));

        // doctrine-like event system with small differences
        $this->containerBuilder->singleton('manager4', 'stdClass')
            ->setLazy(true)
            ->addArgument(new ReferenceDefinition('connection4'));
        $this->containerBuilder->singleton('connection4', 'stdClass')
            ->setPublic($public)
            ->setProperty('listener', [new ReferenceDefinition('listener4')]);
        $this->containerBuilder->singleton('listener4', 'stdClass')
            ->setPublic(true) // just for assertions
            ->addArgument(new ReferenceDefinition('manager4'));

        // private service involved in a loop
        $this->containerBuilder->singleton('foo6', stdClass::class)
            ->setPublic(true)
            ->setProperty('bar6', new ReferenceDefinition('bar6'));
        $this->containerBuilder->singleton('bar6', stdClass::class)
            ->setPublic(false)
            ->addArgument(new ReferenceDefinition('foo6'));
        $this->containerBuilder->singleton('baz6', stdClass::class)
            ->setPublic(true)
            ->setProperty('bar6', new ReferenceDefinition('bar6'));

        // provided by Christian Schiffler
        $this->containerBuilder
            ->singleton('root', stdClass::class)
            ->setArguments([new ReferenceDefinition('level2'), new ReferenceDefinition('multiuse1')])
            ->setPublic(true);
        $this->containerBuilder
            ->singleton('level2', FooForCircularWithAddCalls::class)
            ->addMethodCall('call', [new ReferenceDefinition('level3')]);
        $this->containerBuilder->singleton('multiuse1', stdClass::class);
        $this->containerBuilder
            ->singleton('level3', stdClass::class)
            ->addArgument(new ReferenceDefinition('level4'));
        $this->containerBuilder
            ->singleton('level4', stdClass::class)
            ->setArguments([new ReferenceDefinition('multiuse1'), new ReferenceDefinition('level5')]);
        $this->containerBuilder
            ->singleton('level5', stdClass::class)
            ->addArgument(new ReferenceDefinition('level6'));
        $this->containerBuilder
            ->singleton('level6', FooForCircularWithAddCalls::class)
            ->addMethodCall('call', [new ReferenceDefinition('level5')]);

        $this->proxyDumper = new ProxyDumper();
        $this->arrangePhpParser();

        $this->containerBuilder->compile();

        $this->dumpContainer($functionName = __FUNCTION__ . \ucfirst($visibility));

        $className = $this->getDumperContainerClassName($functionName);

        $dumpedContainerString = $this->getPhpDumper()->dump(\array_merge(
            [
                'class' => $className,
                'namespace' => $this->getNamespace(),
            ],
            $this->dumperOptions
        ));

        $dumpedContainerString = \preg_replace('/valueHolder[a-zA-Z0-9]+/m', 'valueHolder%s', $dumpedContainerString);
        $dumpedContainerString = \preg_replace('/publicProperties[a-zA-Z0-9]+/m', 'publicProperties%s', $dumpedContainerString);
        $dumpedContainerString = \preg_replace('/initializer[a-zA-Z0-9]+/m', 'initializer%s', $dumpedContainerString);

        self::assertStringMatchesFormat(
            $dumpedContainerString,
            \file_get_contents(\rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $className . '.php')
        );

        $foo = $this->container->get('foo');

        self::assertSame($foo, $foo->bar->foobar->foo);

        $foo2 = $this->container->get('foo2');

        self::assertSame($foo2, $foo2->bar->foobar->foo);
        self::assertSame([], (array) $this->container->get('foobar4'));

        $foo5 = $this->container->get('foo5');

        self::assertSame($foo5, $foo5->bar->foo);

        $manager = $this->container->get('manager');

        self::assertEquals(new stdClass(), $manager);

        $manager = $this->container->get('manager2');

        self::assertEquals(new stdClass(), $manager);

        $foo6 = $this->container->get('foo6');

        self::assertEquals((object) ['bar6' => (object) []], $foo6);
        self::assertInstanceOf(stdClass::class, $this->container->get('root'));

        $manager3 = $this->container->get('manager3');
        $listener3 = $this->container->get('listener3');

        self::assertSame($manager3, $listener3->manager, 'Both should identically be the manager3 service');
        // can cause a nesting exception in some cases
        $listener4 = $this->container->get('listener4');

        self::assertInstanceOf('stdClass', $listener4);
    }

    public function provideAlmostCircularCases(): iterable
    {
        yield ['public'];

        yield ['private'];
    }

    public function testContainerCanBeDumpedWithConditionalDefinition(): void
    {
        $this->containerBuilder->singleton('foo', stdClass::class)
            ->addCondition(new ConditionArgument(['\class_exists(\stdClass::class)'], static function (ConditionDefinition $definition): void {
                $definition->setProperty('bar', 'bar');
            }))
            ->setPublic(true);

        $this->containerBuilder->singleton('baz', stdClass::class)
            ->addCondition(new ConditionArgument(['$this->has(\'foo\')'], static function (ConditionDefinition $definition): void {
                $definition->setProperty('foo', new ReferenceDefinition('foo'));
            }))
            ->addCondition(new ConditionArgument(['$this->has(\'bar\')'], static function (ConditionDefinition $definition): void {
                $definition->setProperty('bar', new ReferenceDefinition('bar', ReferenceDefinition::NULL_ON_INVALID_REFERENCE));
                $definition->addMethodCall('foo');
            }))
            ->setPublic(true);

        $this->containerBuilder->singleton('complex', stdClass::class)
            ->addCondition(new ConditionArgument(['$this->has(\'foo\') && \class_exists(\stdClass::class) && $instance instanceof \stdClass'], static function (ConditionDefinition $definition): void {
                $definition->setProperty('foo', new ReferenceDefinition('foo'));
            }))
            ->setPublic(true);

        $this->containerBuilder->singleton(
            'closure',
            static function () {
                return 'test';
            }
        )
            ->addCondition(new ConditionArgument(['\class_exists(\stdClass::class)'], static function (ConditionDefinition $definition): void {
                $definition->addMethodCall('bar');
            }))
            ->setPublic(true);

        $this->arrangePhpParser();

        $this->containerBuilder->compile();

        self::assertStringContainsString('Removed condition from [closure]; reason: Definition is missing implementation of [Viserio\Contract\Container\Definition\MethodCallsAwareDefinition] or [Viserio\Contract\Container\Definition\PropertiesAwareDefinition] interface.', $this->containerBuilder->getLogs()[0]);

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertSame('bar', $this->container->get('foo')->bar);
        self::assertInstanceOf(stdClass::class, $this->container->get('baz')->foo);
        self::assertFalse(\property_exists($this->container->get('baz'), 'bar'));
    }

    public function testInlineSelfRef(): void
    {
        $bar = (new ObjectDefinition('App\Bar', stdClass::class, 1))
            ->setProperty('foo', new ReferenceDefinition('App\Foo'));
        $baz = (new ObjectDefinition('App\Baz', stdClass::class, 1))
            ->setProperty('bar', $bar)
            ->addArgument($bar);

        $this->containerBuilder->singleton('App\Foo', stdClass::class)
            ->setPublic(true)
            ->addArgument($baz);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);
    }

    public function testDumpHandlesLiteralClassWithRootNamespace(): void
    {
        $this->containerBuilder->singleton('foo', '\\stdClass')
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertInstanceOf('stdClass', $this->container->get('foo'));
    }

    public function testDumpHandlesObjectClassNames(): void
    {
        $this->containerBuilder->setParameter('class', 'stdClass');

        $this->containerBuilder->singleton('foo', '{class}');
        $this->containerBuilder->singleton('bar', stdClass::class)
            ->addArgument(new ReferenceDefinition('foo'))
            ->setPublic(true);

        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertInstanceOf('stdClass', $this->container->get('bar'));
    }

    public function testUninitializedSyntheticReference(): void
    {
        $this->containerBuilder->singleton('foo', 'stdClass')
            ->setPublic(true)
            ->setSynthetic(true);
        $this->containerBuilder->bind('bar', 'stdClass')
            ->setPublic(true)
            ->setProperty('foo', new ReferenceDefinition('foo', ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE));

        $this->containerBuilder->setParameter('container.dumper.inline_class_loader', true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertEquals((object) ['foo' => null], $this->container->get('bar'));

        $this->container->set('foo', (object) [123]);

        self::assertEquals((object) ['foo' => (object) [123]], $this->container->get('bar'));
    }

    public function testContainerCanBeDumpedWithCallStaticFactory(): void
    {
        $this->containerBuilder->singleton('foo', [CallFixture::class, '__callStatic'])
            ->setArguments([
                'setSomething',
                [
                    'test',
                    'test2',
                ],
            ])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        $class = $this->container->get('foo');

        self::assertSame('test', $class->setterParam1);
        self::assertSame('test2', $class->setterParam2);
    }

    public function testContainerCanBeDumpedWithCallFactory(): void
    {
        $this->containerBuilder->singleton('foo', [new CallFixture(), '__call'])
            ->setArguments([
                'setSomething',
                [
                    'test',
                    'test2',
                ],
            ])
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        $class = $this->container->get('foo');

        self::assertSame('test', $class->setterParam1);
        self::assertSame('test2', $class->setterParam2);
    }

    /**
     * This test checks the trigger of a deprecation note and should not be removed in major releases.
     *
     * @group legacy
     * @expectedDeprecation The [foo] service is deprecated. You should stop using it, as it will be removed in the future.
     */
    public function testPrivateServiceTriggersDeprecation(): void
    {
        $this->containerBuilder->singleton('foo', 'stdClass')
            ->setPublic(false)
            ->setDeprecated(true);
        $this->containerBuilder->singleton('bar', 'stdClass')
            ->setPublic(true)
            ->setProperty('foo', new ReferenceDefinition('foo'));
        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        $this->container->get('bar');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Compiled' . \DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function assertDumpedContainer(?string $functionName): void
    {
        $this->dumpContainer($functionName);

        parent::assertDumpedContainer($functionName);
    }

    /**
     * @param array $array
     * @param mixed $expectedKey
     * @param mixed $expectedValue
     *
     * @return void
     */
    private function assertArrayContent(array $array, $expectedKey, $expectedValue): void
    {
        self::assertArrayHasKey($expectedKey, $array);

        $value = $array[$expectedKey];

        if (\is_array($value)) {
            foreach ($expectedValue as $key => $v) {
                $this->assertArrayContent($value, $key, $v);
            }
        } elseif (\is_object($value) && \strpos(\get_class($value), "class@anonymous\0") !== false) {
            self::assertSame($expectedValue->test, $value->test);
        } elseif (\is_object($value)) {
            self::assertEquals($expectedValue, $value);
        } else {
            self::assertSame($expectedValue, $value);
        }
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder1
     */
    private function arrangeContainerEntries(ContainerBuilder $builder1): void
    {
        $builder1->bind('factory_class', function () {
            return new FactoryClass();
        });
        $builder1->bind('class', EmptyClass::class);
        $builder1->bind('ano', new class() {
        });
        $builder1->bind('array', ['test' => 'foo']);
    }

    private function arrangePhpParser(): void
    {
        $this->phpParser = (new ParserFactory())->create(
            ParserFactory::ONLY_PHP7,
            new Emulative([
                'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
            ])
        );
        $this->prettyPrinter = new PrettyPrinter();
    }

    private function arrangeFilesContainerConfiguration(): void
    {
        // factory
        $this->containerBuilder->singleton('foo', [FooClass::class, 'getInstance'])
            ->setProperties(
                [
                    'foo' => ['bar', true],
                    'moo' => new ReferenceDefinition('foo.baz'),
                    'qux' => [' {foo}' => 'foo is {foo}', 'foobar' => ' {foo}'],
                ]
            )
            ->addTag('foo', ['foo' => 'foo'])
            ->addTag('foo', ['bar' => 'bar', 'baz' => 'baz'])
            ->setArguments(['foo', new ReferenceDefinition('foo.baz'), [' {foo}' => 'foo is {foo}', 'foobar' => ' {foo}'], true, new ReferenceDefinition(ContainerInterface::class)])
            ->addMethodCall('setBar', [new ReferenceDefinition('bar')])
            ->addMethodCall('initialize')
            ->setPublic(true);

        // method calls
        $this->containerBuilder->singleton('method_call1', FooClass::class)
            ->addMethodCall('setBar', [new ReferenceDefinition('foo')])
            ->addMethodCall('setBar', [new ReferenceDefinition('foo2', ReferenceDefinition::NULL_ON_INVALID_REFERENCE)])
            ->addMethodCall('setBar', [new ReferenceDefinition('foo3', ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)])
            ->addMethodCall('setBar', [new ReferenceDefinition('foobaz', ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)])
            ->setPublic(true);

        // parameter placeholder
        $this->containerBuilder->singleton('foo.baz', ['{baz_class}', 'getInstance'])
            ->setPublic(true);
        $this->containerBuilder->singleton('bar', FooClass::class)
            ->setArguments(['foo', new ReferenceDefinition('foo.baz'), '{foo_bar}'])
            ->setPublic(true);
        $this->containerBuilder->bind('foo_bar', '{foo_class}')
            ->addArgument(new ReferenceDefinition('deprecated_service'))
            ->setPublic(true);

        // parameter
        $this->containerBuilder->setParameter('baz_class', 'BazClass');
        $this->containerBuilder->setParameter('foo_class', FooClass::class);
        $this->containerBuilder->setParameter('foo', 'bar');

        // inlined
        $this->containerBuilder->singleton('foo_with_inline', Foo::class)
            ->addMethodCall('setBar', [new ReferenceDefinition('inlined')])
            ->setPublic(true);
        $this->containerBuilder->singleton('inlined', Bar::class)
            ->setProperty('pub', 'pub')
            ->addMethodCall('setBaz', [new ReferenceDefinition('baz')])
            ->setPublic(false);
        $this->containerBuilder->singleton('baz', Baz::class)
            ->addMethodCall('setFoo', [new ReferenceDefinition('foo_with_inline')])
            ->setPublic(true);

        // synthetic
        $this->containerBuilder->singleton('request', RequestInterface::class)
            ->setSynthetic(true)
            ->setPublic(true);

        // decorted
        $this->containerBuilder->singleton('decorated', stdClass::class)
            ->setPublic(true);
        $this->containerBuilder->singleton('decorator_service', stdClass::class)
            ->decorate('decorated')
            ->setPublic(true);
        $this->containerBuilder
            ->singleton('decorator_service_with_name', stdClass::class)
            ->decorate('decorated', 'decorated.pif-pouf')
            ->setPublic(true);

        $this->containerBuilder->singleton('deprecated_service', stdClass::class)
            ->setDeprecated(true)
            ->setPublic(true);

        // factories
        $this->containerBuilder->singleton('new_factory', FactoryClass::class)
            ->setProperty('foo', 'bar');
        $this->containerBuilder->singleton('factory_service', [new ReferenceDefinition('foo.baz'), 'getInstance'])
            ->setPublic(true);
        $this->containerBuilder->singleton('new_factory_service', [new ReferenceDefinition('new_factory'), 'getInstance'])
            ->setPublic(true);
        $this->containerBuilder->singleton('service_from_static_method', [FactoryClass::class, 'staticCreate'])
            ->setPublic(true);
        $this->containerBuilder->singleton('factory_simple', FactoryClass::class)
            ->addArgument('foo')
            ->setDeprecated(true)
            ->setPublic(false);
        $this->containerBuilder->singleton('factory_service_simple', [new ReferenceDefinition('factory_simple'), 'getInstance'])
            ->setPublic(true);

        // lazy
        $this->containerBuilder->singleton('lazy_context', LazyContext::class)
            ->setArguments([new IteratorArgument(['k1' => new ReferenceDefinition('foo.baz'), 'k2' => new ReferenceDefinition(ContainerInterface::class)]), new IteratorArgument([])])
            ->setPublic(true);
        $this->containerBuilder->singleton('lazy_context_ignore_invalid_ref', LazyContext::class)
            ->setArguments([new IteratorArgument([new ReferenceDefinition('foo.baz'), new ReferenceDefinition('invalid', ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)]), new IteratorArgument([])])
            ->setPublic(true);

        $this->containerBuilder->singleton('BAR', stdClass::class)
            ->setProperty('bar', new ReferenceDefinition('bar'))
            ->setPublic(true);
        $this->containerBuilder->singleton('bar2', stdClass::class)
            ->setPublic(true);
        $this->containerBuilder->singleton('BAR2', stdClass::class)
            ->setPublic(true);

        $this->containerBuilder->setAlias('foo', 'alias_for_foo')
            ->setPublic(true);
    }
}
