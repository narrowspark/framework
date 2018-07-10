<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Viserio\Component\Console\Command\AbstractCommand;

class OutputCommand extends AbstractCommand
{
    /**
     * @var null|string The default command name
     */
    protected static $defaultName = 'output';

    public function handle(): int
    {
        $this->getOutput()->write('Hello World!');

        return 0;
    }
}
