<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Bridge\Monolog\Formatter\ConsoleFormatter;
use Viserio\Bridge\Monolog\Handler\ConsoleHandler;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\WebServer\Exception\RuntimeException;

final class ServerLogCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'server:log';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'server:log
        [-H|--host=0.0.0.0 : The hostname to listen to.]
        [-p|--port=9911 : The port to listen to.]
        [--format= : The line format.]
        [--date-format= : The date format.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Starts a log server that displays logs in real time.';

    /**
     * Colors of the background.
     *
     * @var array
     */
    private static $bgColor = ['black', 'blue', 'cyan', 'green', 'magenta', 'red', 'white', 'yellow'];

    /**
     * A ConsoleHandler instance.
     *
     * @var \Viserio\Bridge\Monolog\Handler\ConsoleHandler
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $output        = $this->getOutput();
        $this->handler = new ConsoleHandler($output);
        $format        = $this->hasOption('format') ? $this->option('format') : ConsoleFormatter::SIMPLE_FORMAT;
        $dateFormat    = $this->hasOption('date-format') ? $this->option('date-format') : ConsoleFormatter::SIMPLE_DATE;

        $this->handler->setFormatter(new ConsoleFormatter([
            'format'      => \str_replace('\n', "\n", $format),
            'date_format' => $dateFormat,
            'colors'      => $output->isDecorated(),
            'multiline'   => OutputInterface::VERBOSITY_DEBUG <= $output->getVerbosity(),
        ]));

        $host    = $this->option('host');
        $port    = $this->option('port');
        $address = 'tcp://' . $host . ':' . $port;

        if (! $socket = \stream_socket_server($address, $errno, $errstr)) {
            throw new RuntimeException(\sprintf('Server start failed on "%s": %s %s.', $address, $errstr, $errno));
        }

        $output->success(\sprintf(
            'Server listening on %s',
            $host !== '0.0.0.0' ? $host . ':' . $port : 'all interfaces, port ' . $port
        ));

        foreach ($this->getLogs($socket) as $clientId => $message) {
            $record = \unserialize(\base64_decode($message, true));

            // Impossible to decode the message, give up.
            if ($record === false) {
                continue;
            }

            $this->displayLog($output, $clientId, $record);
        }

        return 0;
    }

    /**
     * Get collected logs.
     *
     * @param resource $socket
     *
     * @return null|\Generator
     */
    private function getLogs($socket): ?\Generator
    {
        $sockets = [\fstat($socket)['size'] => $socket];
        $write   = [];

        while (true) {
            $read = $sockets;
            \stream_select($read, $write, $write, null);

            foreach ($read as $stream) {
                $size = \fstat($stream)['size'];

                if ($socket === $stream) {
                    $stream                 = \stream_socket_accept($socket);
                    $sockets[$size]         = $stream;
                } elseif (\feof($stream)) {
                    unset($sockets[$size]);

                    \fclose($stream);
                } else {
                    yield $size => \fgets($stream);
                }
            }
        }
    }

    /**
     * Display the record as log message.
     *
     * @param OutputInterface $output
     * @param int             $clientId
     * @param array           $record
     *
     * @return void
     */
    private function displayLog(OutputInterface $output, int $clientId, array $record): void
    {
        if ($this->handler->isHandling($record)) {
            if (isset($record['log_id'])) {
                $clientId = \unpack('H*', $record['log_id'])[1];
            }

            $logBlock = \sprintf('<bg=%s> </>', self::$bgColor[$clientId % 8]);

            $output->write($logBlock);
        }

        $this->handler->handle($record);
    }
}
