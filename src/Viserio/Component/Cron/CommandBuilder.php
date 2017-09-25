<?php
declare(strict_types=1);
namespace Viserio\Component\Cron;

use Viserio\Component\Contract\Cron\Cron as CronContract;

final class CommandBuilder
{
    /**
     * Build the command for running the event in the foreground.
     *
     * @param \Viserio\Component\Contract\Cron\Cron $cron
     *
     * @return string
     */
    public function buildForegroundCommand(CronContract $cron, bool $shouldAppendOutput): string
    {
        $output    = \escapeshellarg($cron->getOutput());
        $redirect  = $shouldAppendOutput ? ' >> ' : ' > ';
        $command   = $cron->getCommand() . $redirect . $output . ($this->isWindows() ? ' 2>&1' : ' 2>&1 &');

        return $this->ensureCorrectUser($cron, $command);
    }

    /**
     * Build the command for running the event in the background.
     *
     * @param \Viserio\Component\Contract\Cron\Cron $cron
     *
     * @return string
     */
    public function buildBackgroundCommand(CronContract $cron): string
    {
    }

    /**
     * Finalize the event's command syntax with the correct user.
     *
     * @param \Viserio\Component\Contract\Cron\Cron $cron
     * @param string                                 $command
     *
     * @return string
     */
    private function ensureCorrectUser(CronContract $cron, string $command): string
    {
        if ($cron->getUser() && ! $this->isWindows()) {
            return 'sudo -u ' . $cron->getUser() . ' -- sh -c \'' . $command . '\'';
        }

        // http://de2.php.net/manual/en/function.exec.php#56599
        // The "start" command will start a detached process, a similar effect to &. The "/B" option prevents
        // start from opening a new terminal window if the program you are running is a console application.
        if ($cron->getUser() && $this->isWindows()) {
            // https://superuser.com/questions/42537/is-there-any-sudo-command-for-windows
            // Options for runas : [{/profile|/noprofile}] [/env] [/netonly] [/smartcard] [/showtrustlevels] [/trustlevel] /user:UserAccountName
            return 'runas ' . $cron->getUser() . 'start /B ' . $command;
        }

        if ($this->isWindows()) {
            return 'start /B ' . $command;
        }

        return $command;
    }

    /**
     * Check if os is windows.
     *
     * @return bool
     */
    private function isWindows(): bool
    {
        return \mb_strtolower(\mb_substr(PHP_OS, 0, 3)) === 'win';
    }
}
