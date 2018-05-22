<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tests\Fixture\InvokeCallableTestClass;
use Viserio\Component\Contract\Container\Container as ContainerContract;

class CompiledContainerTest extends BaseContainerTest
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The container cannot be compiled: [123-abc] is not a valid PHP class name.
     */
    public function testCompilingToAnInvalidClassNameThrowsAnError(): void
    {
        $builder = new ContainerBuilder();
        $builder->enableCompilation(self::COMPILATION_DIR, '123-abc');
        $builder->build();
    }

    public function testTheSameContainerCanBeRecreatedMultipleTimes(): void
    {
        $builder = $this->compiledContainerBuilder;
        $builder->instance('foo', 'bar');

        // The container can be built twice without error
        self::assertInstanceOf(ContainerContract::class, $builder->build());
        self::assertInstanceOf(ContainerContract::class, $builder->build());
    }

    public function testTheContainerIsCompiledOnceAndNeverRecompiledAfter(): void
    {
        // Create a first compiled container in the file
        $builder = $this->compiledContainerBuilder;
        $builder->instance('foo', 'bar');
        $builder->build();

        // Create a second compiled container in the same file but with a DIFFERENT configuration
        $builder = $this->compiledContainerBuilder;
        $builder->instance('foo', 'DIFFERENT');

        $container = $builder->build();

        // The second container is actually using the config of the first because the container was already compiled
        // (the compiled file already existed so the second container did not recompile into it)
        // This behavior is obvious for performance reasons.
        self::assertEquals('bar', $container->get('foo'));
    }

    public function testContainerBuilderCanCompileExtenders(): void
    {
        $builder = $this->compiledContainerBuilder;
        $builder->instance('foo', 'bar');
        $builder->instance('be', 'be_');
        $builder->extend('foo', function ($previous) {
            return 'DIFFERENT_' . $previous;
        });
        $builder->extend('foo', function ($previous, $container) {
            return $container->get('be') . $previous;
        });

        $container = $builder->build();

        self::assertEquals('be_DIFFERENT_bar', $container->get('foo'));
    }

    public function testCompiledContainerHasOneExtendMethod(): void
    {
        $builder = $this->compiledContainerBuilder;
        $builder->instance('foo', 'bar');
        $builder->instance('be', 'be_');
        $builder->extend('foo', function ($previous) {
            return 'DIFFERENT_' . $previous;
        });
        $builder->extend('be', function ($previous) {
            return 'foo_' . $previous;
        });

        $container = $builder->build();

        self::assertEquals('DIFFERENT_bar', $container->get('foo'));
        self::assertEquals('foo_be_', $container->get('be'));
    }

    public function testContainerBuilderCanCompileAInvokableClass(): void
    {
        $builder = $this->compiledContainerBuilder;
        $builder->instance('foo', InvokeCallableTestClass::class);

        $container = $builder->build();

        self::assertEquals(42, $container->get('foo'));
    }
}
