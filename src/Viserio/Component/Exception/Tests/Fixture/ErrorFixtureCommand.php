<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Fixture;

use Viserio\Component\Console\Command\AbstractCommand;

class ErrorFixtureCommand extends AbstractCommand
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
