<?php
declare(strict_types=1);
namespace Viserio\Console\Traits;

use Closure;

trait ConfirmableTrait
{
    /**
     * Confirm before proceeding with the action.
     *
     * @param string             $warning
     * @param \Closure|bool|null $callback
     *
     * @return bool
     */
    public function confirmToProceed($warning = 'Application is in Production mode!', $callback = null): bool
    {
        $callback = is_null($callback) ? $this->getDefaultConfirmCallback() : $callback;
        $shouldConfirm = $callback instanceof Closure ? call_user_func($callback) : $callback;

        if ($shouldConfirm) {
            if ($this->option('force')) {
                return true;
            }

            $this->comment(str_repeat('*', strlen($warning) + 12));
            $this->comment('*     '.$warning.'     *');
            $this->comment(str_repeat('*', strlen($warning) + 12));
            $this->output->writeln('');

            $confirmed = $this->confirm('Do you really wish to run this command?');

            if (! $confirmed) {
                $this->comment('Command Cancelled!');

                return false;
            }
        }

        return true;
    }

    /**
     * Get the default confirmation callback.
     *
     * @return \Closure
     */
    protected function getDefaultConfirmCallback(): Closure
    {
        return function () {
            $container = $this->getContainer();

            if ($container->has('env')) {
                return $container->get('env') == 'production';
            }

            return $container->get('app.env') == 'production';
        };
    }
}
