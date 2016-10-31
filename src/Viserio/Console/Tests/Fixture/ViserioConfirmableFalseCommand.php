<?php
declare(strict_types=1);
namespace Viserio\Console\Tests\Fixture;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Viserio\Console\Command\Command;
use Viserio\Console\Traits\ConfirmableTrait;

class ViserioConfirmableFalseCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'confirmable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'confirmable command';

    public function confirm(string $question, bool $default = false)
    {
        return false;
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run.'],
        ];
    }

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 'not';
        }
    }
}
