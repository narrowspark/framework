<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Provider\Twig\Command\CleanCommand;

class CleanCommandTest extends MockeryTestCase
{
    public function testFailed(): void
    {
        $files = $this->mock(FilesystemContract::class);
        $files->shouldReceive('deleteDirectory')
            ->once()
            ->with(__DIR__);
        $files->shouldReceive('exists')
            ->once()
            ->with(__DIR__)
            ->andReturn(true);
        $container = new ArrayContainer([
            FilesystemContract::class => $files,
            'config'                  => [
                'viserio' => [
                    'view' => [
                        'engines' => [
                            'twig' => [
                                'options' => [
                                    'cache' => __DIR__,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $command = new CleanCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertContains('Twig cache failed to be cleaned.', $output);
    }

    public function testSuccess(): void
    {
        $files = $this->mock(FilesystemContract::class);
        $files->shouldReceive('deleteDirectory')
            ->once()
            ->with(__DIR__);
        $files->shouldReceive('exists')
            ->once()
            ->andReturn(false);
        $container = new ArrayContainer([
            FilesystemContract::class       => $files,
            'config'                        => [
                'viserio' => [
                    'view' => [
                        'engines' => [
                            'twig' => [
                                'options' => [
                                    'cache' => __DIR__,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $command = new CleanCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertContains('Twig cache cleaned.', $output);
    }
}
