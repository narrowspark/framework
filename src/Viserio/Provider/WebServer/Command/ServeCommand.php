<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer\Command;

use Throwable;
use Viserio\Component\Console\Command\Command;

class ServeCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'server:run';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Runs a local web server.';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        if ($documentRoot = $input->getOption('docroot') === null) {
            $this->error('The document root directory must be either passed as first argument of the constructor or through the "--docroot" input option.');

            return 1;
        }

        if (!is_dir($documentRoot)) {
            $this->error(sprintf('The document root directory "%s" does not exist.', $documentRoot));

            return 1;
        }

        $callback = null;
        $disableOutput = false;
        $output = $this->getOutput();
        $console = $this;

        if ($output->isQuiet()) {
            $disableOutput = true;
        } else {
            $callback = function ($type, $buffer) use ($output, $console) {
                if (Process::ERR === $type && $output instanceof ConsoleOutputInterface) {
                    $output = $output->getErrorOutput();
                }

                $console->line($buffer, false, OutputInterface::OUTPUT_RAW);
            };
        }

        try {
            $server = new WebServer();
            $config = new WebServerConfig($documentRoot, $env, $input->getArgument('addressport'), $input->getOption('router'));

            $output->success(sprintf('Server listening on http://%s', $config->getAddress()));
            $this->comment('Quit the server with CONTROL-C.');

            $exitCode = $server->run($config, $disableOutput, $callback);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        return $exitCode;
    }
}
