<?php

declare(strict_types=1);
namespace Viserio\Console\Tests\Fixture;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Viserio\Console\Command\Command;

class ViserioCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'demo:greet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Greet someone';

    public function handle()
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
