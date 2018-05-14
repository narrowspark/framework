<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use Nyholm\NSA;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tests\Fixture\ContainerContractFixtureInterface;
use Viserio\Component\Container\Tests\Fixture\ContainerDefaultValueFixture;
use Viserio\Component\Container\Tests\Fixture\CustomParentContainer;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class CompiledContainerTest extends BaseContainerTest
{
    public function testCompilingToAnInvalidClassNameThrowsAnError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The container cannot be compiled: [123-abc] is not a valid PHP class name.');

        $this->unCompiledContainerBuilder->enableCompilation(self::COMPILATION_DIR, '123-abc');
        $this->unCompiledContainerBuilder->build();
    }

    public function testTheCompiledContainerCanExtendACustomClass(): void
    {
        $builder = new ContainerBuilder();
        $builder->enableCompilation(
            self::COMPILATION_DIR,
            self::generateCompiledClassName(),
            // Customize the parent class
            CustomParentContainer::class
        );

        $container = $builder->build();

        static::assertInstanceOf(CustomParentContainer::class, $container);
    }

    public function testTheSameContainerCanBeRecreatedMultipleTimes(): void
    {
        $this->compiledContainerBuilder->instance('foo', 'bar');

        // The container can be built twice without error
        static::assertInstanceOf(ContainerContract::class, $this->compiledContainerBuilder->build());
        static::assertInstanceOf(ContainerContract::class, $this->compiledContainerBuilder->build());
    }

    public function testTheContainerIsCompiledOnceAndNeverRecompiledAfter(): void
    {
        // Create a first compiled container in the file
        $this->compiledContainerBuilder->instance('foo', 'bar');
        $this->compiledContainerBuilder->build();

        // Create a second compiled container in the same file but with a DIFFERENT configuration
        $this->compiledContainerBuilder->instance('foo', 'DIFFERENT');

        $container = $this->compiledContainerBuilder->build();

        // The second container is actually using the config of the first because the container was already compiled
        // (the compiled file already existed so the second container did not recompile into it)
        // This behavior is obvious for performance reasons.
        static::assertEquals('bar', $container->get('foo'));
        // The not compiled container
        static::assertEquals('DIFFERENT', $this->compiledContainerBuilder->get('foo'));
    }

    public function testContainerBuilderCanCompileExtenders(): void
    {
        $this->compiledContainerBuilder->instance('foo', 'bar');
        $this->compiledContainerBuilder->instance('be', 'be_');
        $this->compiledContainerBuilder->extend('foo', function ($container, $previous) {
            return 'DIFFERENT_' . $previous;
        });
        $this->compiledContainerBuilder->extend('foo', function ($container, $previous) {
            return $container->get('be') . $previous;
        });

        $container = $this->compiledContainerBuilder->build();

        static::assertEquals('be_DIFFERENT_bar', $container->get('foo'));
    }

    public function testCompiledContainerHasOneExtendMethod(): void
    {
        $this->compiledContainerBuilder->instance('foo', 'bar');
        $this->compiledContainerBuilder->instance('be', 'be_');
        $this->compiledContainerBuilder->extend('foo', function ($container, $previous) {
            return 'DIFFERENT_' . $previous;
        });
        $this->compiledContainerBuilder->extend('be', function ($container, $previous) {
            return 'foo_' . $previous;
        });

        $container = $this->compiledContainerBuilder->build();

        static::assertEquals('DIFFERENT_bar', $container->get('foo'));
        static::assertEquals('foo_be_', $container->get('be'));
        static::assertSame(
            1,
            \mb_substr_count(self::getCompiledContainerContent($container), '$extender($this, $binding);')
        );
    }

    public function testEmptyAnonymousClassesCanBeCompiled(): void
    {
        $this->compiledContainerBuilder->instance('ano', new class() {
        });

        $container = $this->compiledContainerBuilder->build();

        static::assertSame('object', \gettype($container->get('ano')));
    }

    // @todo find bug in better-reflection
//    public function testAnonymousClassesWithInterfaceCanBeCompiled(): void
//    {
//        $this->compiledContainerBuilder->instance('ano', new class() implements ContainerContractFixtureInterface {
//        });
//
//        $container = $this->compiledContainerBuilder->build();
//
//        static::assertSame('object', \gettype($container->get('ano')));
//    }

// @todo parse code to find the arguments for the anonymous class
//    public function testAnonymousClassesWithDefaultConstructorCanBeCompiled(): void
//    {
//        $builder->instance('ano', new class() {
//            private $text = '';
//
//            public function __construct(?string $text = 'test')
//            {
//                $this->text = $text;
//            }
//
//            /**
//             * @return string
//             */
//            public function getText(): string
//            {
//                return $this->text;
//            }
//        });
//
//        $container = $this->compiledContainerBuilder->build();
//
//        static::assertSame('test', $container->get('ano')->getText());
//        static::assertSame('test', $builder->get('ano')->getText());
//    }

    // @todo parse code to find the arguments for the anonymous class
//    public function testAnonymousClassesWithConstructorCanBeCompiled(): void
//    {
//        $class = new class('test') {
//            private $text = '';
//
//            public function __construct(string $text)
//            {
//                $this->text = $text;
//            }
//
//            /**
//             * @return string
//             */
//            public function getText(): string
//            {
//                return $this->text;
//            }
//        };
//
//        $builder->instance('ano', $class);
//
//        $container = $this->compiledContainerBuilder->build();
//
//        self::assertSame('test', $container->get('ano')->getText());
//        self::assertSame('test', $builder->get('ano')->getText());
//    }

    public function testCallableWithUseClass(): void
    {
        $this->compiledContainerBuilder->bind('be', function () {
            return new FactoryClass();
        });

        $container = $this->compiledContainerBuilder->build();

        static::assertInstanceOf(FactoryClass::class, $container->get('be'));
    }

    public function testCompiledContainerIsIdempotent(): void
    {
        $compiledContainerClass1 = self::generateCompiledClassName();
        $compiledContainerClass2 = self::generateCompiledClassName();

        $this->compiledContainerBuilder->bind('factory_class', function () {
            return new FactoryClass();
        });
        $this->compiledContainerBuilder->instance('class', ContainerDefaultValueFixture::class);
        $this->compiledContainerBuilder->instance('ano', new class() {
        });
        $this->compiledContainerBuilder->instance('array', ['test' => 'foo']);

        $this->compiledContainerBuilder = new ContainerBuilder();
        $this->compiledContainerBuilder->enableCompilation(self::COMPILATION_DIR, $compiledContainerClass1);
        $container1 = $this->compiledContainerBuilder->build();

        $builder = new ContainerBuilder();
        $builder->enableCompilation(self::COMPILATION_DIR, $compiledContainerClass2);
        $container2 = $builder->build();

        // The method mapping of the resulting CompiledContainers should be equal
        static::assertEquals(NSA::getProperty($container1, 'methodMapping'), NSA::getProperty($container2, 'methodMapping'));
    }
}
