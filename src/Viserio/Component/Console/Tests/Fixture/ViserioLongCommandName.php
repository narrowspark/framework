<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Viserio\Component\Console\Command\Command;

class ViserioLongCommandName extends Command
{
    protected static $defaultName = 'thisIsALongName:hallo';

    protected $description = 'Greet someone';

    public function handle(): void
    {
    }
}
