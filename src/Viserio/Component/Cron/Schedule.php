<?php
declare(strict_types=1);
namespace Viserio\Component\Cron;

use LogicException;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessUtils;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Cron\Cron as CronContract;

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
     * Path for the working directory.
     *
     * @var string
     */
    protected $workingDirPath;

    /**
     * The cache store implementation.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    /**
     * Set the mutex path.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $path
     * @param null|string                       $consoleName
     */
    public function __construct(CacheItemPoolInterface $cache, string $path, string $consoleName = null)
    {
        $this->cache          = $cache;
        $this->workingDirPath = $path;
        $this->console        = $consoleName;
    }

    /**
     * Add a new callback cron job to the schedule.
     *
     * @param string|callable $callback
     * @param array           $parameters
     *
     * @return \Viserio\Component\Cron\CallbackCron
     */
    public function call($callback, array $parameters = []): CallbackCron
    {
        $cron = new CallbackCron($this->cache, $callback, $parameters);
        $cron->setPath($this->workingDirPath);

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
     * @return \Viserio\Component\Contracts\Cron\Cron
     */
    public function command(string $command, array $parameters = []): CronContract
    {
        if (class_exists($command) && $this->container !== null) {
            $command = $this->getContainer()->get($command)->getName();
        }

        $binary = ProcessUtils::escapeArgument((string) (new PhpExecutableFinder())->find(false));

        if (defined('CEREBRO_BINARY')) {
            $console = Application::cerebroBinary();
        } elseif ($this->console !== null) {
            $console = ProcessUtils::escapeArgument($this->console);
        } else {
            // @codeCoverageIgnoreStart
            throw new LogicException('You need to set a console name or a path to a console, befor you call command.');
            // @codeCoverageIgnoreEnd
        }

        return $this->exec(sprintf('%s %s %s', $binary, $console, $command), $parameters);
    }

    /**
     * Add a new executable command cron job to the schedule.
     *
     * @param string $command
     * @param array  $parameters
     *
     * @return \Viserio\Component\Contracts\Cron\Cron
     */
    public function exec(string $command, array $parameters = []): CronContract
    {
        if (count($parameters)) {
            $command .= ' ' . $this->compileParameters($parameters);
        }

        $cron = new Cron($this->cache, $command);
        $cron->setPath($this->workingDirPath);

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
     * @param string $environment
     * @param bool   $isMaintenance
     *
     * @return array
     */
    public function dueCronJobs(string $environment, bool $isMaintenance = false): array
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

            return is_numeric($key) ? $value : sprintf('%s=%s', $key, $value);
        }, $parameters, $keys);

        return implode(' ', array_combine($keys, $items));
    }
}
