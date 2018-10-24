<?php
declare(strict_types=1);
namespace Viserio\Component\Cron;

use Symfony\Component\Process\PhpExecutableFinder;
use Viserio\Component\Console\Application;
use Viserio\Component\Contract\Cache\Traits\CacheItemPoolAwareTrait;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Cron\Cron as CronContract;
use Viserio\Component\Contract\Cron\Exception\LogicException;
use Viserio\Component\Contract\Cron\Schedule as ScheduleContract;

class Schedule implements ScheduleContract
{
    use ContainerAwareTrait;
    use CacheItemPoolAwareTrait;

    /**
     * All of the cron jobs on the schedule.
     *
     * @var array
     */
    private $jobs = [];

    /**
     * Console path or console name that should be called.
     *
     * @var null|string
     */
    private $console;

    /**
     * Path for the working directory.
     *
     * @var string
     */
    private $workingDirPath;

    /**
     * Create a new Schedule instance.
     *
     * @param string      $path        path for the working directory
     * @param null|string $consoleName
     */
    public function __construct(string $path, ?string $consoleName = null)
    {
        $this->workingDirPath = $path;
        $this->console        = $consoleName;
    }

    /**
     * {@inheritdoc}
     */
    public function call($callback, array $parameters = []): CallbackCron
    {
        $cron = new CallbackCron($callback, $parameters);

        if ($this->cachePool !== null) {
            $cron->setCacheItemPool($this->getCacheItemPool());
        }

        $cron->setPath($this->workingDirPath);

        if ($this->container !== null) {
            $cron->setContainer($this->container);
        }

        $this->jobs[] = $cron;

        return $cron;
    }

    /**
     * {@inheritdoc}
     */
    public function command(string $command, array $parameters = []): CronContract
    {
        if ($this->container !== null && $this->container->has($command)) {
            $command = $this->container->get($command)->getName();
        }

        if (\defined('CEREBRO_BINARY')) {
            return $this->exec(Application::formatCommandString($command), $parameters);
        }

        if ($this->console !== null) {
            $binary  = \escapeshellarg((string) (new PhpExecutableFinder())->find(false));
            $console = \escapeshellarg($this->console);

            return $this->exec(\sprintf('%s %s %s', $binary, $console, $command), $parameters);
        }

        throw new LogicException('You need to set a console name or a path to a console, before you call command.');
    }

    /**
     * {@inheritdoc}
     */
    public function exec(string $command, array $parameters = []): CronContract
    {
        if (\count($parameters) !== 0) {
            $command .= ' ' . $this->compileParameters($parameters);
        }

        $cron = new Cron($command);

        if ($this->cachePool !== null) {
            $cron->setCacheItemPool($this->getCacheItemPool());
        }

        $cron->setPath($this->workingDirPath);

        if ($this->container !== null) {
            $cron->setContainer($this->container);
        }

        $this->jobs[] = $cron;

        return $cron;
    }

    /**
     * {@inheritdoc}
     */
    public function getCronJobs(): array
    {
        return $this->jobs;
    }

    /**
     * {@inheritdoc}
     */
    public function dueCronJobs(string $environment, bool $isMaintenance = false): array
    {
        return \array_filter($this->jobs, function (CronContract $job) use ($environment, $isMaintenance) {
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
        $keys = \array_keys($parameters);

        $items = \array_map(function ($value, $key) {
            if (\is_array($value)) {
                $value = \array_map('escapeshellarg', $value);

                $value = \implode(' ', $value);
            } elseif (! \is_numeric($value) && ! \preg_match('/^(-.$|--.*)/', $value)) {
                $value = \escapeshellarg($value);
            }

            return \is_numeric($key) ? $value : \sprintf('%s=%s', $key, $value);
        }, $parameters, $keys);

        return \implode(' ', \array_combine($keys, $items));
    }
}
