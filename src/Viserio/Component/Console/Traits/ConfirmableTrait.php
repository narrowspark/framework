<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Traits;

use Closure;

/**
 * @method null|array|string option($key = null)
 * @method void              comment(string $string, $verbosityLevel = null)
 * @method void              line(string $string, ?string $style = null, $verbosityLevel = null)
 */
trait ConfirmableTrait
{
    /**
     * Confirm before proceeding with the action.
     *
     * @param string             $warning
     * @param null|bool|\Closure $callback
     *
     * @return bool
     */
    public function confirmToProceed(string $warning = 'Application is in Production mode!', $callback = null): bool
    {
        $callback      = $callback ?? $this->getDefaultConfirmCallback();
        $shouldConfirm = $callback instanceof Closure ? $callback() : $callback;

        if ($shouldConfirm) {
            if ($this->option('force')) {
                return true;
            }

            $this->comment(\str_repeat('*', \mb_strlen($warning) + 12));
            $this->comment('*     ' . $warning . '     *');
            $this->comment(\str_repeat('*', \mb_strlen($warning) + 12));
            $this->line('');

            $confirmed = $this->confirm('Do you really wish to run this command?');

            if (! $confirmed) {
                $this->comment('Command Cancelled!');

                return false;
            }
        }

        return true;
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $question
     * @param bool   $default
     *
     * @return bool|string
     */
    abstract public function confirm(string $question, bool $default = false);

    /**
     * Get the default confirmation callback.
     *
     * @return \Closure
     */
    protected function getDefaultConfirmCallback(): Closure
    {
        return function () {
            if ($this->container !== null && $this->container->has('env')) {
                return $this->container->get('env') === 'prod';
            }

            if (($env = \getenv('APP_ENV')) !== false) {
                return $env === 'prod';
            }

            return true;
        };
    }
}
