<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Compiler\Container\CompiledContainer;
use Viserio\Component\Container\Container;
use Viserio\Component\Container\ContainerBuilder;

/**
 * @internal
 */
abstract class BaseContainerTest extends TestCase
{
    /**
     * @var string
     */
    protected const COMPILATION_DIR = __DIR__ . \DIRECTORY_SEPARATOR . 'compiled';

    /**
     * @var \Viserio\Component\Container\ContainerBuilder
     */
    protected $unCompiledContainerBuilder;

    /**
     * @var \Viserio\Component\Container\ContainerBuilder
     */
    protected $compiledContainerBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $builder = new ContainerBuilder();

        $this->unCompiledContainerBuilder = $builder;

        $builder->enableCompilation(
            static::COMPILATION_DIR,
            static::generateCompiledClassName(),
            CompiledContainer::class,
            __NAMESPACE__
        );

        $this->compiledContainerBuilder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

//        \array_map('unlink', \glob(static::COMPILATION_DIR . '/*'));
//
//        @\rmdir(static::COMPILATION_DIR);
    }

    public function provideContainer(): array
    {
        return [
            'not-compiled' => [
                new ContainerBuilder(),
            ],
            'compiled' => [
                (new ContainerBuilder())->enableCompilation(
                    static::COMPILATION_DIR,
                    static::generateCompiledClassName(),
                    CompiledContainer::class,
                    __NAMESPACE__
                ),
            ],
        ];
    }

    /**
     * @return string
     */
    protected static function generateCompiledClassName(): string
    {
        return 'Container' . \uniqid();
    }

    /**
     * @param object $container
     *
     * @return string
     */
    protected static function getCompiledContainerContent(object $container): string
    {
        return \file_get_contents(
            self::COMPILATION_DIR . '/' . \str_replace(__NAMESPACE__ . '\\', '', \get_class($container)) . '.php'
        );
    }
}
