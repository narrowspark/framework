<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Command;

use Cake\Chronos\Chronos;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;

class DownCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'app:down';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'app:down
        [--message= : The message for the maintenance mode.]
        [--retry= : The number of seconds after which the request may be retried.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Put the application into maintenance mode';

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $kernel = $this->getContainer()->get(ConsoleKernelContract::class);

        \file_put_contents(
            $kernel->getStoragePath('framework/down'),
            \json_encode($this->getDownPayload(), \JSON_PRETTY_PRINT)
        );

        $this->comment('Application is now in maintenance mode.');

        return 0;
    }

    /**
     * Get the information that are put into the "down" file.
     *
     * @return array
     */
    protected function getDownPayload(): array
    {
        return [
            'time'    => Chronos::now()->getTimestamp(),
            'message' => $this->option('message'),
            'retry'   => $this->getRetryTime(),
        ];
    }

    /**
     * Get the number of seconds the client should wait before retrying their request.
     *
     * @return null|int
     */
    protected function getRetryTime(): ?int
    {
        $retry = $this->option('retry');

        return \is_numeric($retry) && $retry > 0 ? (int) $retry : null;
    }
}
