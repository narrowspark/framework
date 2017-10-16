<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Viserio\Component\Console\Command\Command;

class ViserioCommand extends Command
{
    protected static $defaultName = 'demo:greet';

    protected $description = 'Greet someone';

    public function handle(): void
    {
        $this->addArgument(
            'name',
            InputArgument::OPTIONAL,
            'Who do you want to greet?'
        );

        $this->addOption(
            '--yell',
            null,
            InputOption::VALUE_NONE,
            'If set, the task will yell in uppercase letters'
        );
    }
}
