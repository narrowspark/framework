<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Foundation\Console\Commands\DownCommand;

class DownCommandTest extends MockeryTestCase
{
    public function testCommand()
    {
        $path      = __DIR__ . '/../../Fixtures';
        $framework = $path . '/framework';

        if (! is_dir($framework)) {
            mkdir($framework);
        }

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->with('path.storage')
            ->andReturn($path);

        $container = new ArrayContainer([
            RepositoryContract::class => $config,
        ]);

        $command = new DownCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Application is now in maintenance mode.\n", $output);

        $data = json_decode(file_get_contents($framework . '/down'), true);

        self::assertTrue(is_int($data['time']));
        // self::assertSame('', $data['message']);
        // self::assertSame('', $data['retry']);

        if (is_file($framework . '/down')) {
            @unlink($framework . '/down');
        }

        if (is_dir($framework)) {
            rmdir($framework);
        }
    }
}
