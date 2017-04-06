<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Foundation\Console\Commands\UpCommand;

class UpCommandTest extends MockeryTestCase
{
    public function testCommand()
    {
        $path      = __DIR__ . '/../../Fixtures';
        $framework = $path . '/framework';

        if (! is_dir($framework)) {
            mkdir($framework);
        }

        file_put_contents($framework . '/down', 'test');

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->with('path.storage')
            ->andReturn($path);

        $container = new ArrayContainer([
            RepositoryContract::class => $config,
        ]);

        $command = new UpCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Application is now live.\n", $output);

        if (is_dir($framework)) {
            rmdir($framework);
        }
    }
}
