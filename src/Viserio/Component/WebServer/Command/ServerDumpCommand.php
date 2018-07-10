<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Command;

use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;
use Symfony\Component\VarDumper\Command\Descriptor\HtmlDescriptor;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Server\DumpServer;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\WebServer\Exception\RuntimeException;
use Viserio\Component\Support\Debug\HtmlDumper;

final class ServerDumpCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'server:dump';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'server:dump
        [-H|--host=127.0.0.1 : The hostname to listen to.]
        [-p|--port=8000 : The port to listen to.]
        [--format=cli: The output format.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Starts a dump server that collects and displays dumps in a single place.';

    /**
     * @var \Symfony\Component\VarDumper\Server\DumpServer
     */
    private $server;

    /**
     * @var \Symfony\Component\VarDumper\Command\Descriptor\DumpDescriptorInterface[]
     */
    private $descriptors;

    /**
     * Create a new DumpServerCommand instance.
     *
     * @param \Symfony\Component\VarDumper\Server\DumpServer $server
     */
    public function __construct(DumpServer $server)
    {
        $this->server      = $server;
        $this->descriptors = [
            'cli'  => new CliDescriptor(new CliDumper()),
            'html' => new HtmlDescriptor(new HtmlDumper()),
        ];

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $format     = $this->option('format');
        $output     = $this->getOutput();

        if (! $descriptor = $this->descriptors[$format] ?? null) {
            throw new RuntimeException(\sprintf('Unsupported format [%s].', $format));
        }

        $this->error('Symfony Var Dumper Server');

        $this->server->start();

        $output->success(\sprintf('Server listening on %s', $this->server->getHost()));
        $this->comment('Quit the server with CONTROL-C.');

        $this->server->listen(function (Data $data, array $context, int $clientId) use ($descriptor, $output) {
            $descriptor->describe($output, $data, $context, $clientId);
        });

        return 0;
    }
}
