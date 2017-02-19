<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Symfony\Component\Console\Input\InputOption;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Console\Traits\ConfirmableTrait;

class ViserioConfirmableTrueCommand extends Command
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
        return true;
    }

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 'not';
        }
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run.'],
        ];
    }
}
