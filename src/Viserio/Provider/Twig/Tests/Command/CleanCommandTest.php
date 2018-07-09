<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Support\Invoker;
use Viserio\Provider\Twig\Command\CleanCommand;

/**
 * @internal
 */
final class CleanCommandTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Console\Command\Command
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $command = new CleanCommand();
        $command->setInvoker(new Invoker());

        $this->command = $command;
    }

    public function testFailed(): void
    {
        $path  = __DIR__;
        $files = $this->mock(FilesystemContract::class);
        $files->shouldReceive('deleteDirectory')
            ->once()
            ->with($path);
        $files->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);
        $container = new ArrayContainer([
            FilesystemContract::class => $files,
            'config'                  => [
                'viserio' => [
                    'view' => [
                        'engines' => [
                            'twig' => [
                                'options' => [
                                    'cache' => $path,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->command->setContainer($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        static::assertContains('Twig cache failed to be cleaned.', $output);
    }

    public function testSuccess(): void
    {
        $path  = __DIR__;
        $files = $this->mock(FilesystemContract::class);
        $files->shouldReceive('deleteDirectory')
            ->once()
            ->with($path);
        $files->shouldReceive('has')
            ->once()
            ->andReturn(false);
        $container = new ArrayContainer([
            FilesystemContract::class => $files,
            'config'                  => [
                'viserio' => [
                    'view' => [
                        'engines' => [
                            'twig' => [
                                'options' => [
                                    'cache' => $path,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->command->setContainer($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        static::assertContains('Twig cache cleaned.', $output);
    }
}
