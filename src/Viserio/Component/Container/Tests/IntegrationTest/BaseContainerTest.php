<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Compiler\Container\CompiledContainer;
use Viserio\Component\Container\ContainerBuilder;

abstract class BaseContainerTest extends TestCase
{
    /**
     * @var string
     */
    protected const COMPILATION_DIR = __DIR__ . '/compiled';

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

        \array_map('unlink', \glob(static::COMPILATION_DIR . '/*'));

        @\rmdir(static::COMPILATION_DIR);
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
}
