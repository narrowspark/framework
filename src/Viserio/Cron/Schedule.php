<?php
declare(strict_types=1);
namespace Viserio\Cron;

use LogicException;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Cron\Cron as CronContract;

class Schedule
{
    use ContainerAwareTrait;

    /**
     * All of the cron jobs on the schedule.
     *
     * @var array
     */
    protected $jobs = [];

    /**
     * Console path or console name that should be called.
     *
     * @var string|null
     */
    protected $console;

    /**
     * Set the console name or path that should call the commands.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setConsoleName(string $name): Schedule
    {
        $this->console = $name;

        return $this;
    }

    /**
     * Add a new callback cron job to the schedule.
     *
     * @param string $callback
     * @param array  $parameters
     *
     * @return \Viserio\Cron\CallbackCron
     */
    public function call(string $callback, array $parameters = []): CallbackCron
    {
        $cron = new CallbackCron($callback, $parameters);

        if ($this->container !== null) {
            $cron->setContainer($this->getContainer());
        }

        $this->jobs[] = $cron;

        return $cron;
    }

    /**
     * Add a new command cron job to the schedule.
     *
     * @param string $command
     * @param array  $parameters
     *
     * @return \Viserio\Contracts\Cron\Cron
     */
    public function command(string $command, array $parameters = []): CronContract
    {
        if (class_exists($command) && $this->container !== null) {
            $command = $this->getContainer()->get($command)->getName();
        }

        $binary = ProcessUtils::escapeArgument((new PhpExecutableFinder())->find(false));

        if (defined('CEREBRO_BINARY')) {
            $console = ProcessUtils::escapeArgument(CEREBRO_BINARY);
        } elseif ($this->console !== null) {
            $console = $this->console;
        } else {
            throw new LogicException('You need to set a console name or a path to a console.');
        }

        return $this->exec("{$binary} {$console} {$command}", $parameters);
    }

    /**
     * Add a new executable command cron job to the schedule.
     *
     * @param string $command
     * @param array  $parameters
     *
     * @return \Viserio\Contracts\Cron\Cron
     */
    public function exec($command, array $parameters = []): CronContract
    {
        if (count($parameters)) {
            $command .= ' ' . $this->compileParameters($parameters);
        }

        $cron = new Cron($command);

        if ($this->container !== null) {
            $cron->setContainer($this->getContainer());
        }

        $this->jobs[] = $cron;

        return $cron;
    }

    /**
     * Get all of the events on the schedule.
     *
     * @return array
     */
    public function getCronJobs(): array
    {
        return $this->jobs;
    }

    /**
     * Get all of the cron jobs on the schedule that are due.
     *
     * @param stirng $environment
     * @param bool   $isMaintenance
     *
     * @return array
     */
    public function dueCronJobs(string $environment, bool $isMaintenance): array
    {
        return array_filter($this->jobs, function ($job) use ($environment, $isMaintenance) {
            return $job->isDue($environment, $isMaintenance);
        });
    }

    /**
     * Compile parameters for a command.
     *
     * @param array $parameters
     *
     * @return string
     */
    protected function compileParameters(array $parameters): string
    {
        $keys = array_keys($parameters);

        $items = array_map(function ($value, $key) {
            if (is_array($value)) {
                $value = array_map(function ($value) {
                    return ProcessUtils::escapeArgument($value);
                }, $value);

                $value = implode(' ', $value);
            } elseif (! is_numeric($value) && ! preg_match('/^(-.$|--.*)/i', $value)) {
                $value = ProcessUtils::escapeArgument($value);
            }

            return is_numeric($key) ? $value : "{$key}={$value}";
        }, $parameters, $keys);

        return implode(' ', array_combine($keys, $items));
    }
}
