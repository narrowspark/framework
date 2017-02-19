<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Commands;

use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Bridge\Twig\Commands\CleanCommand;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class CleanCommandTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testFailed()
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
            OptionsResolver::class          => new OptionsResolver(),
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

        $output = $tester->getDisplay();

        self::assertContains('Twig cache failed to be cleaned.', $output);
    }

    public function testSuccess()
    {
        $files = $this->mock(FilesystemContract::class);
        $files->shouldReceive('deleteDirectory')
            ->once()
            ->with(__DIR__);
        $files->shouldReceive('exists')
            ->once()
            ->andReturn(false);
        $container = new ArrayContainer([
            OptionsResolver::class          => new OptionsResolver(),
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

        $output = $tester->getDisplay();

        self::assertContains('Twig cache cleaned.', $output);
    }
}
