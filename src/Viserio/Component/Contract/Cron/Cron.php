<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Cron;

use Closure;

interface Cron
{
    /**
     * Get the command name.
     *
     * @return string
     */
    public function getCommand(): string;

    /**
     * Set which user the command should run as.
     *
     * @param string $user
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function setUser(string $user): self;

    /**
     * Get which user runs the command.
     *
     * @return string
     */
    public function getUser(): string;

    /**
     * Limit the environments the command should run in.
     *
     * @param array|mixed $environments
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function setEnvironments($environments): self;

    /**
     * Determine if the cron runs in the given environment.
     *
     * @param string $environment
     *
     * @return bool
     */
    public function runsInEnvironment(string $environment): bool;

    /**
     * Run the given cron job.
     *
     * @return mixed
     */
    public function run();

    /**
     * State that the command should run in background.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function runInBackground(): self;

    /**
     * Build the command string.
     *
     * @return string
     */
    public function buildCommand(): string;

    /**
     * Do not allow the cron job to overlap each other.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function withoutOverlapping(): self;

    /**
     * Append the output of the command to a given location.
     *
     * @param string $location
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function appendOutputTo(string $location): self;

    /**
     * Send the output of the command to a given location.
     *
     * @param string $location
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function sendOutputTo(string $location): self;

    /**
     * Determine if the given cron job should run based on the Cron expression.
     *
     * @param string $environment
     * @param bool   $isMaintenance
     *
     * @return bool
     */
    public function isDue(string $environment, bool $isMaintenance = false): bool;

    /**
     * Get the cron expression for the cron job.
     *
     * @return string
     */
    public function getExpression(): string;

    /**
     * The Cron expression representing the cron's frequency.
     *
     * @param string $expression
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function cron(string $expression): self;

    /**
     * Schedule the cron job to run hourly.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function hourly(): self;

    /**
     * Schedule the cron job to run daily.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function daily(): self;

    /**
     * Schedule the cron job to run monthly.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function monthly(): self;

    /**
     * Schedule the cron job to run yearly.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function yearly(): self;

    /**
     * Schedule the cron job to run quarterly.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function quarterly(): self;

    /**
     * Schedule the cron job to run every minute.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function everyMinute(): self;

    /**
     * Schedule the cron job to run every five minutes.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function everyFiveMinutes(): self;

    /**
     * Schedule the cron job to run every ten minutes.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function everyTenMinutes(): self;

    /**
     * Schedule the event to run every fifteen minutes.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function everyFifteenMinutes(): self;

    /**
     * Schedule the cron job to run every thirty minutes.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function everyThirtyMinutes(): self;

    /**
     * Set the days of the week the command should run on.
     *
     * @param array|int|string $days
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function days($days): self;

    /**
     * Schedule the cron job to run monthly on a given day and time.
     *
     * @param int    $day
     * @param string $time
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function monthlyOn(int $day = 1, string $time = '0:0'): self;

    /**
     * Schedule the cron job to run daily at a given time (10:00, 19:30, etc).
     *
     * @param string $time
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function dailyAt(string $time): self;

    /**
     * Schedule the cron job to run twice daily.
     *
     * @param int $first
     * @param int $second
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function twiceDaily(int $first = 1, int $second = 13): self;

    /**
     * Schedule the cron to run twice monthly.
     *
     * @param int $first
     * @param int $second
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function twiceMonthly(int $first = 1, int $second = 16): self;

    /**
     * Schedule the cron job to run only on weekdays.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function weekdays(): self;

    /**
     * Schedule the cron job to run only on Mondays.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function mondays(): self;

    /**
     * Schedule the cron job to run only on Tuesdays.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function tuesdays(): self;

    /**
     * Schedule the cron job to run only on Wednesdays.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function wednesdays(): self;

    /**
     * Schedule the cron job to run only on Thursdays.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function thursdays(): self;

    /**
     * Schedule the cron job to run only on Fridays.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function fridays(): self;

    /**
     * Schedule the cron job to run only on Saturdays.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function saturdays(): self;

    /**
     * Schedule the cron job to run only on Sundays.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function sundays(): self;

    /**
     * Schedule the cron job to run weekly.
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function weekly(): self;

    /**
     * Schedule the cron job to run weekly on a given day and time.
     *
     * @param int    $day
     * @param string $time
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function weeklyOn(int $day, string $time = '0:0'): self;

    /**
     * Schedule the cron job to run between start and end time.
     *
     * @param string $startTime
     * @param string $endTime
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function between(string $startTime, string $endTime): self;

    /**
     * Schedule the cron job to not run between start and end time.
     *
     * @param string $startTime
     * @param string $endTime
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function unlessBetween(string $startTime, string $endTime): self;

    /**
     * Register a callback to further filter the schedule.
     *
     * @param \Closure $callback
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function when(Closure $callback): self;

    /**
     * Register a callback to further filter the schedule.
     *
     * @param \Closure $callback
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function skip(Closure $callback): self;

    /**
     * Register a callback to be called before the operation.
     *
     * @param \Closure $callback
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function before(Closure $callback): self;

    /**
     * Register a callback to be called after the operation.
     *
     * @param \Closure $callback
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function after(Closure $callback): self;

    /**
     * Set the human-friendly description of the cron.
     *
     * @param string $description
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function setDescription(string $description): self;

    /**
     * Get the summary of the cron for display.
     *
     * @return string
     */
    public function getSummaryForDisplay(): string;

    /**
     * Set the timezone the date should be evaluated on.
     *
     * @param string $timezone
     *
     * @return \Viserio\Component\Contract\Cron\Cron
     */
    public function setTimezone(string $timezone): self;

    /**
     * Determine if the filters pass for the cron.
     *
     * @return bool
     */
    public function filtersPass(): bool;
}
