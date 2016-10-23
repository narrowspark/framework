<?php
declare(strict_types=1);
namespace Viserio\Cron;

use Cake\Chronos\Chronos;
use Closure;
use Cron\CronExpression;
use Viserio\Contracts\Cron\Cron as CronContract;

class Cron implements CronContract
{
    /**
     * The cron expression representing the cron job's frequency.
     *
     * @var string
     */
    public $expression = '* * * * * *';

    /**
     * The command string.
     *
     * @var string
     */
    public $command;

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
    public $output = '/dev/null';

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
    public $description;

    /**
     * The timezone the date should be evaluated on.
     *
     * @var \DateTimeZone|string
     */
    public $timezone;

    /**
     * Create a new cron instance.
     *
     * @param string $command
     */
    public function __construct(string $command)
    {
        $this->command = $command;
        $this->output = $this->getDefaultOutput();
    }

    /**
     * Get the cron expression for the cron job.
     *
     * @return string
     */
    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * The Cron expression representing the cron's frequency.
     *
     * @param string $expression
     *
     * @return $this
     */
    public function cron(string $expression): CronContract
    {
        $this->expression = $expression;

        return $this;
    }

    /**
     * Schedule the cron job to run hourly.
     *
     * @return $this
     */
    public function hourly(): CronContract
    {
        return $this->spliceIntoPosition(1, '0');
    }

    /**
     * Schedule the cron job to run daily.
     *
     * @return $this
     */
    public function daily(): CronContract
    {
        return $this->spliceIntoPosition(1, '0')
            ->spliceIntoPosition(2, '0');
    }

    /**
     * Schedule the cron job to run monthly.
     *
     * @return $this
     */
    public function monthly(): CronContract
    {
        return $this->spliceIntoPosition(1, '0')
            ->spliceIntoPosition(2, '0')
            ->spliceIntoPosition(3, '1');
    }

    /**
     * Schedule the cron job to run yearly.
     *
     * @return $this
     */
    public function yearly(): CronContract
    {
        return $this->spliceIntoPosition(1, '0')
            ->spliceIntoPosition(2, '0')
            ->spliceIntoPosition(3, '1')
            ->spliceIntoPosition(4, '1');
    }

    /**
     * Schedule the cron job to run quarterly.
     *
     * @return $this
     */
    public function quarterly(): CronContract
    {
        return $this->spliceIntoPosition(1, '0')
            ->spliceIntoPosition(2, '0')
            ->spliceIntoPosition(3, '1')
            ->spliceIntoPosition(4, '*/3');
    }

    /**
     * Schedule the cron job to run every minute.
     *
     * @return $this
     */
    public function everyMinute(): CronContract
    {
        return $this->spliceIntoPosition(1, '*');
    }

    /**
     * Schedule the cron job to run every five minutes.
     *
     * @return $this
     */
    public function everyFiveMinutes(): CronContract
    {
        return $this->spliceIntoPosition(1, '*/5');
    }

    /**
     * Schedule the cron job to run every ten minutes.
     *
     * @return $this
     */
    public function everyTenMinutes(): CronContract
    {
        return $this->spliceIntoPosition(1, '*/10');
    }

    /**
     * Schedule the cron job to run every thirty minutes.
     *
     * @return $this
     */
    public function everyThirtyMinutes(): CronContract
    {
        return $this->spliceIntoPosition(1, '0,30');
    }

    /**
     * Set the days of the week the command should run on.
     *
     * @param array|dynamic $days
     *
     * @return $this
     */
    public function days($days): CronContract
    {
        $days = is_array($days) ? $days : func_get_args();

        return $this->spliceIntoPosition(5, implode(',', $days));
    }

    /**
     * Schedule the cron job to run monthly on a given day and time.
     *
     * @param int    $day
     * @param string $time
     *
     * @return $this
     */
    public function monthlyOn(int $day = 1, string $time = '0:0'): CronContract
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, (string) $day);
    }

    /**
     * Schedule the cron job to run daily at a given time (10:00, 19:30, etc).
     *
     * @param string $time
     *
     * @return $this
     */
    public function dailyAt(string $time): CronContract
    {
        $segments = explode(':', $time);

        return $this->spliceIntoPosition(2, (string) $segments[0])
            ->spliceIntoPosition(1, count($segments) == 2 ? (string) $segments[1] : '0');
    }

    /**
     * Schedule the cron job to run twice daily.
     *
     * @param int $first
     * @param int $second
     *
     * @return $this
     */
    public function twiceDaily(int $first = 1, int $second = 13): CronContract
    {
        $hours = $first . ',' . $second;

        return $this->spliceIntoPosition(1, '0')
            ->spliceIntoPosition(2, $hours);
    }

    /**
     * Schedule the cron job to run only on weekdays.
     *
     * @return $this
     */
    public function weekdays(): CronContract
    {
        return $this->spliceIntoPosition(5, '1-5');
    }

    /**
     * Schedule the cron job to run only on Mondays.
     *
     * @return $this
     */
    public function mondays(): CronContract
    {
        return $this->days(1);
    }

    /**
     * Schedule the cron job to run only on Tuesdays.
     *
     * @return $this
     */
    public function tuesdays(): CronContract
    {
        return $this->days(2);
    }

    /**
     * Schedule the cron job to run only on Wednesdays.
     *
     * @return $this
     */
    public function wednesdays(): CronContract
    {
        return $this->days(3);
    }

    /**
     * Schedule the cron job to run only on Thursdays.
     *
     * @return $this
     */
    public function thursdays(): CronContract
    {
        return $this->days(4);
    }

    /**
     * Schedule the cron job to run only on Fridays.
     *
     * @return $this
     */
    public function fridays(): CronContract
    {
        return $this->days(5);
    }

    /**
     * Schedule the cron job to run only on Saturdays.
     *
     * @return $this
     */
    public function saturdays(): CronContract
    {
        return $this->days(6);
    }

    /**
     * Schedule the cron job to run only on Sundays.
     *
     * @return $this
     */
    public function sundays(): CronContract
    {
        return $this->days(0);
    }

    /**
     * Schedule the cron job to run weekly.
     *
     * @return $this
     */
    public function weekly(): CronContract
    {
        return $this->spliceIntoPosition(1, '0')
            ->spliceIntoPosition(2, '0')
            ->spliceIntoPosition(5, '0');
    }

    /**
     * Schedule the cron job to run weekly on a given day and time.
     *
     * @param int    $day
     * @param string $time
     *
     * @return $this
     */
    public function weeklyOn(int $day, string $time = '0:0'): CronContract
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(5, (string) $day);
    }

    /**
     * Schedule the cron job to run between start and end time.
     *
     * @param string $startTime
     * @param string $endTime
     *
     * @return $this
     */
    public function between(string $startTime, string $endTime): CronContract
    {
        return $this->when($this->inTimeInterval($startTime, $endTime));
    }

    /**
     * Schedule the cron job to not run between start and end time.
     *
     * @param string $startTime
     * @param string $endTime
     *
     * @return $this
     */
    public function unlessBetween(string $startTime, string $endTime): CronContract
    {
        return $this->skip($this->inTimeInterval($startTime, $endTime));
    }

    /**
     * Register a callback to further filter the schedule.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function when(Closure $callback): CronContract
    {
        $this->filters[] = $callback;

        return $this;
    }

    /**
     * Register a callback to further filter the schedule.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function skip(Closure $callback): CronContract
    {
        $this->rejects[] = $callback;

        return $this;
    }

    /**
     * Register a callback to be called before the operation.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function before(Closure $callback): CronContract
    {
        $this->beforeCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a callback to be called after the operation.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function after(Closure $callback): CronContract
    {
        $this->afterCallbacks[] = $callback;

        return $this;
    }

    /**
     * Set the human-friendly description of the cron.
     *
     * @param string $description
     *
     * @return $this
     */
    public function description(string $description): CronContract
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the timezone the date should be evaluated on.
     *
     * @param \DateTimeZone|string $timezone
     *
     * @return $this
     */
    public function timezone($timezone): CronContract
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Splice the given value into the given position of the expression.
     *
     * @param int    $position
     * @param string $value
     *
     * @return $this
     */
    protected function spliceIntoPosition(int $position, string $value): CronContract
    {
        $segments = explode(' ', $this->expression);
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
        return (DIRECTORY_SEPARATOR == '\\') ? 'NUL' : '/dev/null';
    }

    /**
     * Determine if the Cron expression passes.
     *
     * @return bool
     */
    protected function expressionPasses(): bool
    {
        $date = Chronos::now();

        if ($this->timezone) {
            $date->setTimezone($this->timezone);
        }

        return CronExpression::factory($this->expression)->isDue($date->toDateTimeString());
    }

    /**
     * Schedule the cron job to run between start and end time.
     *
     * @param string $startTime
     * @param string $endTime
     *
     * @return \Closure
     */
    private function inTimeInterval(string $startTime, string $endTime): Closure
    {
        return function () use ($startTime, $endTime) {
            $now = Chronos::now()->getTimestamp();

            return $now >= strtotime($startTime) && $now <= strtotime($endTime);
        };
    }
}
