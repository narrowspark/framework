<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Fixture;

use Viserio\Component\Console\Command\Command;

class ErrorFixtureCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'error';

    public function handle(): int
    {
        Console::test('error');

        return 1;
    }
}
