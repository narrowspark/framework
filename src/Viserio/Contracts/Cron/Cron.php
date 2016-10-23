<?php
declare(strict_types=1);
namespace Viserio\Contracts\Cron;

use Closure;

interface Cron
{
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
     * @return $this
     */
    public function cron(string $expression): Cron;

    /**
     * Schedule the cron job to run hourly.
     *
     * @return $this
     */
    public function hourly(): Cron;

    /**
     * Schedule the cron job to run daily.
     *
     * @return $this
     */
    public function daily(): Cron;

    /**
     * Schedule the cron job to run monthly.
     *
     * @return $this
     */
    public function monthly(): Cron;

    /**
     * Schedule the cron job to run yearly.
     *
     * @return $this
     */
    public function yearly(): Cron;

    /**
     * Schedule the cron job to run quarterly.
     *
     * @return $this
     */
    public function quarterly(): Cron;

    /**
     * Schedule the cron job to run every minute.
     *
     * @return $this
     */
    public function everyMinute(): Cron;

    /**
     * Schedule the cron job to run every five minutes.
     *
     * @return $this
     */
    public function everyFiveMinutes(): Cron;

    /**
     * Schedule the cron job to run every ten minutes.
     *
     * @return $this
     */
    public function everyTenMinutes(): Cron;

    /**
     * Schedule the cron job to run every thirty minutes.
     *
     * @return $this
     */
    public function everyThirtyMinutes(): Cron;

    /**
     * Set the days of the week the command should run on.
     *
     * @param array|dynamic $days
     *
     * @return $this
     */
    public function days($days): Cron;

    /**
     * Schedule the cron job to run monthly on a given day and time.
     *
     * @param int    $day
     * @param string $time
     *
     * @return $this
     */
    public function monthlyOn(int $day = 1, string $time = '0:0'): Cron;

    /**
     * Schedule the cron job to run daily at a given time (10:00, 19:30, etc).
     *
     * @param string $time
     *
     * @return $this
     */
    public function dailyAt(string $time): Cron;

    /**
     * Schedule the cron job to run twice daily.
     *
     * @param int $first
     * @param int $second
     *
     * @return $this
     */
    public function twiceDaily(int $first = 1, int $second = 13): Cron;

    /**
     * Schedule the cron job to run only on weekdays.
     *
     * @return $this
     */
    public function weekdays(): Cron;

    /**
     * Schedule the cron job to run only on Mondays.
     *
     * @return $this
     */
    public function mondays(): Cron;

    /**
     * Schedule the cron job to run only on Tuesdays.
     *
     * @return $this
     */
    public function tuesdays(): Cron;

    /**
     * Schedule the cron job to run only on Wednesdays.
     *
     * @return $this
     */
    public function wednesdays(): Cron;

    /**
     * Schedule the cron job to run only on Thursdays.
     *
     * @return $this
     */
    public function thursdays(): Cron;

    /**
     * Schedule the cron job to run only on Fridays.
     *
     * @return $this
     */
    public function fridays(): Cron;

    /**
     * Schedule the cron job to run only on Saturdays.
     *
     * @return $this
     */
    public function saturdays(): Cron;

    /**
     * Schedule the cron job to run only on Sundays.
     *
     * @return $this
     */
    public function sundays(): Cron;

    /**
     * Schedule the cron job to run weekly.
     *
     * @return $this
     */
    public function weekly(): Cron;

    /**
     * Schedule the cron job to run between start and end time.
     *
     * @param string $startTime
     * @param string $endTime
     *
     * @return $this
     */
    public function between(string $startTime, string $endTime): Cron;

    /**
     * Schedule the cron job to not run between start and end time.
     *
     * @param string $startTime
     * @param string $endTime
     *
     * @return $this
     */
    public function unlessBetween(string $startTime, string $endTime): Cron;

    /**
     * Register a callback to further filter the schedule.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function when(Closure $callback): Cron;

    /**
     * Register a callback to further filter the schedule.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function skip(Closure $callback): Cron;

    /**
     * Register a callback to be called before the operation.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function before(Closure $callback): Cron;

    /**
     * Register a callback to be called after the operation.
     *
     * @param \Closure $callback
     *
     * @return $this
     */
    public function after(Closure $callback): Cron;

    /**
     * Set the human-friendly description of the cron.
     *
     * @param string $description
     *
     * @return $this
     */
    public function description(string $description): Cron;

    /**
     * Set the timezone the date should be evaluated on.
     *
     * @param \DateTimeZone|string $timezone
     *
     * @return $this
     */
    public function timezone($timezone): Cron;
}
