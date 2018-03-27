<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Viserio\Component\Console\Command\Command;

class ViserioLongCommandName extends Command
{
    protected static $defaultName = 'thisIsALongName:hallo';

    protected $description = 'Greet someone';

    public function handle(): void
    {
    }
}
