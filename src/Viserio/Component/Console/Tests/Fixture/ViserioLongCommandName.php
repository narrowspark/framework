<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Viserio\Component\Console\Command\AbstractCommand;

class ViserioLongCommandName extends AbstractCommand
{
    protected static $defaultName = 'thisIsALongName:hallo';

    protected $description = 'Greet someone';

    public function handle(): int
    {
        return 0;
    }
}
