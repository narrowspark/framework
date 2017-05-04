<?php
declare(strict_types=1);
namespace Viserio\Component\Cron;

use Cake\Chronos\Chronos;
use Closure;
use Cron\CronExpression;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;
use Viserio\Component\Contracts\Cache\Traits\CacheItemPoolAwareTrait;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Cron\Cron as CronContract;
use Viserio\Component\Support\Traits\InvokerAwareTrait;
use Viserio\Component\Support\Traits\MacroableTrait;

class Cron implements CronContract
{
    use ContainerAwareTrait;
    use CacheItemPoolAwareTrait;
    use InvokerAwareTrait;
    use MacroableTrait;

    /**
     * The cron expression representing the cron job's frequency.
     *
     * @var string
     */
    protected $expression = '* * * * * *';

    /**
     * The command string.
     *
     * @var string
     */
    protected $command;

    /**
     * The array of filter callbacks.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * The array of reject callbacks.
     *
     * @var array
     */
    protected $rejects = [];

    /**
     * The location that output should be sent to.
     *
     * @var string
     */
    protected $output;

    /**
     * Indicates whether output should be appended.
     *
     * @var bool
     */
    protected $shouldAppendOutput = false;

    /**
     * The array of callbacks to be run before the cron is started.
     *
     * @var array
     */
    protected $beforeCallbacks = [];

    /**
     * The array of callbacks to be run after the cron is finished.
     *
     * @var array
     */
    protected $afterCallbacks = [];

    /**
     * The human readable description of the cron.
     *
     * @var string
     */
    protected $description;

    /**
     * The timezone the date should be evaluated on.
     *
     * @var string
     */
    protected $timezone;

    /**
     * The list of environments the command should run under.
     *
     * @var array
     */
    protected $environments = [];

    /**
     * Path for the working directory.
     *
     * @var string
     */
    protected $path;

    /**
     * Indicates if the command should run in background.
     *
     * @var bool
     */
    protected $runInBackground = false;

    /**
     * The user the command should run as.
     *
     * @var string
     */
    protected $user;

    /**
     * Indicates if the command should run in maintenance mode.
     *
     * @var bool
     */
    protected $evenInMaintenanceMode = false;

    /**
     * Indicates if the command should not overlap itself.
     *
     * @var bool
     */
    protected $withoutOverlapping = false;

    /**
     * Create a new cron instance.
     *
     * @param string $command
     */
    public function __construct(string $command)
    {
        $this->command = $command;
        $this->output  = $this->getDefaultOutput();
    }

    /**
     * Set working directory.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the working directory.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Determine if the cron job runs in maintenance mode.
     *
     * @return bool
     */
    public function runsInMaintenanceMode(): bool
    {
        return $this->evenInMaintenanceMode;
    }

    /**
     * State that the cron job should run even in maintenance mode.
     *
     * @return $this
     */
    public function evenInMaintenanceMode(): CronContract
    {
        $this->evenInMaintenanceMode = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(string $user): CronContract
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnvironments($environments): CronContract
    {
        $this->environments = is_array($environments) ? $environments : func_get_args();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function runsInEnvironment(string $environment): bool
    {
        return empty($this->environments) || in_array($environment, $this->environments);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->withoutOverlapping) {
            $item = $this->cachePool->getItem($this->getMutexName());
            $item->set($this->getMutexName());
            $item->expiresAfter(1440);

            $this->cachePool->save($item);
        }

        if (! $this->runInBackground) {
            return $this->runCommandInForeground();
        }

        $run = $this->runCommandInBackground();

        if ($this->withoutOverlapping) {
            $this->cachePool->deleteItem($this->getMutexName());
        }

        return $run;
    }

    /**
     * {@inheritdoc}
     */
    public function runInBackground(): CronContract
    {
        $this->runInBackground = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildCommand(): string
    {
        $output    = ProcessUtils::escapeArgument($this->output);
        $redirect  = $this->shouldAppendOutput ? ' >> ' : ' > ';
        $command   = $this->command . $redirect . $output . ($this->isWindows() ? ' 2>&1' : ' 2>&1 &');

        return $this->ensureCorrectUser($command);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutOverlapping(): CronContract
    {
        $this->withoutOverlapping = true;

        return $this->skip(function () {
            return $this->cachePool->hasItem($this->getMutexName());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function sendOutputTo(string $location): CronContract
    {
        $this->output = $location;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function appendOutputTo(string $location): CronContract
    {
        $this->output             = $location;
        $this->shouldAppendOutput = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDue(string $environment, bool $isMaintenance = false): bool
    {
        if (! $this->runsInMaintenanceMode() && $isMaintenance) {
            return false;
        }

        return $this->expressionPasses() && $this->runsInEnvironment($environment);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * {@inheritdoc}
     */
    public function cron(string $expression): CronContract
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hourly(): CronContract
    {
        return $this->spliceIntoPosition(1, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function daily(): CronContract
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function monthly(): CronContract
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function yearly(): CronContract
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function quarterly(): CronContract
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, '*/3');
    }

    /**
     * {@inheritdoc}
     */
    public function everyMinute(): CronContract
    {
        return $this->spliceIntoPosition(1, '*');
    }

    /**
     * {@inheritdoc}
     */
    public function everyFiveMinutes(): CronContract
    {
        return $this->spliceIntoPosition(1, '*/5');
    }

    /**
     * {@inheritdoc}
     */
    public function everyTenMinutes(): CronContract
    {
        return $this->spliceIntoPosition(1, '*/10');
    }

    /**
     * {@inheritdoc}
     */
    public function everyThirtyMinutes(): CronContract
    {
        return $this->spliceIntoPosition(1, '0,30');
    }

    /**
     * {@inheritdoc}
     */
    public function days($days): CronContract
    {
        $days = is_array($days) ? $days : func_get_args();

        return $this->spliceIntoPosition(5, implode(',', $days));
    }

    /**
     * {@inheritdoc}
     */
    public function monthlyOn(int $day = 1, string $time = '0:0'): CronContract
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $day);
    }

    /**
     * {@inheritdoc}
     */
    public function dailyAt(string $time): CronContract
    {
        $segments = explode(':', $time);

        return $this->spliceIntoPosition(2, (int) $segments[0])
            ->spliceIntoPosition(1, count($segments) == 2 ? (int) $segments[1] : 0);
    }

    /**
     * {@inheritdoc}
     */
    public function twiceDaily(int $first = 1, int $second = 13): CronContract
    {
        $hours = $first . ',' . $second;

        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, $hours);
    }

    /**
     * {@inheritdoc}
     */
    public function weekdays(): CronContract
    {
        return $this->spliceIntoPosition(5, '1-5');
    }

    /**
     * {@inheritdoc}
     */
    public function mondays(): CronContract
    {
        return $this->days(1);
    }

    /**
     * {@inheritdoc}
     */
    public function tuesdays(): CronContract
    {
        return $this->days(2);
    }

    /**
     * {@inheritdoc}
     */
    public function wednesdays(): CronContract
    {
        return $this->days(3);
    }

    /**
     * {@inheritdoc}
     */
    public function thursdays(): CronContract
    {
        return $this->days(4);
    }

    /**
     * {@inheritdoc}
     */
    public function fridays(): CronContract
    {
        return $this->days(5);
    }

    /**
     * {@inheritdoc}
     */
    public function saturdays(): CronContract
    {
        return $this->days(6);
    }

    /**
     * {@inheritdoc}
     */
    public function sundays(): CronContract
    {
        return $this->days(0);
    }

    /**
     * {@inheritdoc}
     */
    public function weekly(): CronContract
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(5, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function weeklyOn(int $day, string $time = '0:0'): CronContract
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(5, $day);
    }

    /**
     * {@inheritdoc}
     */
    public function between(string $startTime, string $endTime): CronContract
    {
        return $this->when($this->inTimeInterval($startTime, $endTime));
    }

    /**
     * {@inheritdoc}
     */
    public function unlessBetween(string $startTime, string $endTime): CronContract
    {
        return $this->skip($this->inTimeInterval($startTime, $endTime));
    }

    /**
     * {@inheritdoc}
     */
    public function when(Closure $callback): CronContract
    {
        $this->filters[] = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function skip(Closure $callback): CronContract
    {
        $this->rejects[] = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function before(Closure $callback): CronContract
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function after(Closure $callback): CronContract
    {
        $this->afterCallbacks[] = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(string $description): CronContract
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummaryForDisplay(): string
    {
        if (is_string($this->description)) {
            return $this->description;
        }

        return $this->buildCommand();
    }

    /**
     * {@inheritdoc}
     */
    public function setTimezone(string $timezone): CronContract
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function filtersPass(): bool
    {
        foreach ($this->filters as $callback) {
            if (! $this->getInvoker()->call($callback)) {
                return false;
            }
        }

        foreach ($this->rejects as $callback) {
            if ($this->getInvoker()->call($callback)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if os is windows.
     *
     * @return bool
     */
    protected function isWindows(): bool
    {
        return mb_strtolower(mb_substr(PHP_OS, 0, 3)) === 'win';
    }

    /**
     * Finalize the event's command syntax with the correct user.
     *
     * @param string $command
     *
     * @return string
     */
    protected function ensureCorrectUser(string $command): string
    {
        if ($this->user && ! $this->isWindows()) {
            return 'sudo -u ' . $this->user . ' -- sh -c \'' . $command . '\'';
        }

        // http://de2.php.net/manual/en/function.exec.php#56599
        // The "start" command will start a detached process, a similar effect to &. The "/B" option prevents
        // start from opening a new terminal window if the program you are running is a console application.
        if ($this->user && $this->isWindows()) {
            // https://superuser.com/questions/42537/is-there-any-sudo-command-for-windows
            // Options for runas : [{/profile|/noprofile}] [/env] [/netonly] [/smartcard] [/showtrustlevels] [/trustlevel] /user:UserAccountName

            return 'runas ' . $this->user . 'start /B ' . $command;
        } elseif ($this->isWindows()) {
            return 'start /B ' . $command;
        }

        return $command;
    }

    /**
     * Splice the given value into the given position of the expression.
     *
     * @param int        $position
     * @param int|string $value
     *
     * @return \Viserio\Component\Contracts\Cron\Cron
     */
    protected function spliceIntoPosition(int $position, $value): CronContract
    {
        $segments                = explode(' ', $this->expression);
        $segments[$position - 1] = $value;

        return $this->cron(implode(' ', $segments));
    }

    /**
     * Get the default output depending on the OS.
     *
     * @return string
     */
    protected function getDefaultOutput(): string
    {
        return $this->isWindows() ? 'NUL' : '/dev/null';
    }

    /**
     * Determine if the Cron expression passes.
     *
     * @return bool
     */
    protected function expressionPasses(): bool
    {
        $date = Chronos::now();

        if ($this->timezone !== null) {
            $date->setTimezone($this->timezone);
        }

        return CronExpression::factory($this->expression)->isDue($date->toDateTimeString());
    }

    /**
     * Run the command in the foreground.
     *
     * @return int The exit status code
     */
    protected function runCommandInForeground(): int
    {
        $this->callBeforeCallbacks();

        $process = new Process(
            trim($this->buildCommand(), ' &'),
            $this->path,
            null,
            null,
            null
        );

        $run = $process->run();

        $this->callAfterCallbacks();

        return $run;
    }

    /**
     * Run the command in the background.
     *
     * @return int The exit status code
     */
    protected function runCommandInBackground(): int
    {
        $process = new Process(
            $this->buildCommand(),
            $this->path,
            null,
            null,
            null
        );

        return $process->run();
    }

    /**
     * Call all of the "before" callbacks for the cron job.
     */
    protected function callBeforeCallbacks()
    {
        foreach ($this->beforeCallbacks as $callback) {
            $this->getInvoker()->call($callback);
        }
    }

    /**
     * Call all of the "after" callbacks for the cron job.
     */
    protected function callAfterCallbacks()
    {
        foreach ($this->afterCallbacks as $callback) {
            $this->getInvoker()->call($callback);
        }
    }

    /**
     * Get the mutex name for the scheduled command.
     *
     * @return string
     */
    protected function getMutexName(): string
    {
        return 'schedule-' . sha1($this->expression . $this->command);
    }

    /**
     * Schedule the cron job to run between start and end time.
     *
     * @param string $startTime
     * @param string $endTime
     *
     * @return \Closure
     */
    protected function inTimeInterval(string $startTime, string $endTime): Closure
    {
        return function () use ($startTime, $endTime) {
            $now = Chronos::now()->getTimestamp();

            return $now >= strtotime($startTime) && $now <= strtotime($endTime);
        };
    }
}
