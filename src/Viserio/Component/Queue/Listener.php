<?php
declare(strict_types=1);
namespace Viserio\Component\Queue;

use Closure;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

class Listener
{
    /**
     * The console name.
     *
     * @var string
     */
    protected $consoleName;

    /**
     * The command working path.
     *
     * @var string
     */
    protected $commandPath;

    /**
     * The environment the workers should run under.
     *
     * @var string
     */
    protected $environment;

    /**
     * The amount of seconds to wait before polling the queue.
     *
     * @var int
     */
    protected $sleep = 3;

    /**
     * The amount of times to try a job before logging it failed.
     *
     * @var int
     */
    protected $maxTries = 0;

    /**
     * The queue worker command line.
     *
     * @var string
     */
    protected $workerCommand;

    /**
     * The output handler callback.
     *
     * @var \Closure|null
     */
    protected $outputHandler;

    /**
     * Create a new queue listener.
     *
     * @param string $commandPath
     * @param string $consoleName
     */
    public function __construct(string $commandPath, string $consoleName = 'cerebro')
    {
        $this->commandPath   = $commandPath;
        $this->workerCommand = $this->buildWorkerCommand();
        $this->consoleName   = $consoleName;
    }

    /**
     * Listen to the given queue connection.
     *
     * @param string $connection
     * @param string $queue
     * @param string $delay
     * @param string $memory
     * @param int    $timeout
     */
    public function listen(string $connection, string $queue, string $delay, string $memory, int $timeout = 60)
    {
        $process = $this->createProcess($connection, $queue, $delay, $memory, $timeout);

        while (true) {
            $this->runProcess($process, $memory);
        }
    }

    /**
     * Run the given process.
     *
     * @param \Symfony\Component\Process\Process $process
     * @param int                                $memory
     */
    public function runProcess(Process $process, int $memory)
    {
        $process->run(function ($type, $line) {
            $this->handleWorkerOutput($type, $line);
        });

        // Once we have run the job we'll go check if the memory limit has been
        // exceeded for the script. If it has, we will kill this script so a
        // process manager will restart this with a clean slate of memory.
        if ($this->memoryExceeded($memory)) {
            $this->stop();
        }
    }

    /**
     * Create a new Symfony process for the worker.
     *
     * @param string $connection
     * @param string $queue
     * @param int    $delay
     * @param int    $memory
     * @param int    $timeout
     *
     * @return \Symfony\Component\Process\Process
     */
    public function createProcess(
        string $connection,
        string $queue,
        int $delay,
        int $memory,
        int $timeout
    ): Process {
        $string = $this->workerCommand;

        // If the environment is set, we will append it to the command string so the
        // workers will run under the specified environment. Otherwise, they will
        // just run under the production environment which is not always right.
        if (isset($this->environment)) {
            $string .= ' --env=' . ProcessUtils::escapeArgument($this->environment);
        }

        // Next, we will just format out the worker commands with all of the various
        // options available for the command. This will produce the final command
        // line that we will pass into a Symfony process object for processing.
        $command = sprintf(
            $string,
            ProcessUtils::escapeArgument($connection),
            ProcessUtils::escapeArgument($queue),
            $delay,
            $memory,
            $this->sleep,
            $this->maxTries
        );

        return new Process($command, $this->commandPath, null, null, $timeout);
    }

    /**
     * Determine if the memory limit has been exceeded.
     *
     * @param int $memoryLimit
     *
     * @return bool
     */
    public function memoryExceeded(int $memoryLimit): bool
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * Stop listening and bail out of the script.
     *
     *
     * @codeCoverageIgnore
     */
    public function stop()
    {
        die;
    }

    /**
     * Set the output handler callback.
     *
     * @param \Closure $outputHandler
     */
    public function setOutputHandler(Closure $outputHandler)
    {
        $this->outputHandler = $outputHandler;
    }

    /**
     * Get the current listener environment.
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Set the current environment.
     *
     * @param string $environment
     */
    public function setEnvironment(string $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Get the amount of seconds to wait before polling the queue.
     *
     * @return int
     *
     * @codeCoverageIgnore
     */
    public function getSleep(): int
    {
        return $this->sleep;
    }

    /**
     * Set the amount of seconds to wait before polling the queue.
     *
     * @param int $sleep
     */
    public function setSleep(int $sleep)
    {
        $this->sleep = $sleep;
    }

    /**
     * Set the amount of times to try a job before logging it failed.
     *
     * @param int $tries
     */
    public function setMaxTries(int $tries)
    {
        $this->maxTries = $tries;
    }

    /**
     * Build the environment specific worker command.
     *
     * @return string
     */
    protected function buildWorkerCommand(): string
    {
        $binary = ProcessUtils::escapeArgument((new PhpExecutableFinder())->find(false));

        $console = ProcessUtils::escapeArgument($this->consoleName);
        $command = 'queue:work %s --queue=%s --delay=%s --memory=%s --sleep=%s --tries=%s';

        return "{$binary} {$console} {$command}";
    }

    /**
     * Handle output from the worker process.
     *
     * @param int    $type
     * @param string $line
     */
    protected function handleWorkerOutput(int $type, string $line)
    {
        if (isset($this->outputHandler)) {
            call_user_func($this->outputHandler, $type, $line);
        }
    }
}
