<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\WebServer\Command;

use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;
use Symfony\Component\VarDumper\Command\Descriptor\HtmlDescriptor;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Server\DumpServer;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Contract\WebServer\Exception\RuntimeException;

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
        [--format=cli : The output format.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Starts a dump server that collects and displays dumps in a single place.';

    /** @var \Symfony\Component\VarDumper\Server\DumpServer */
    private $server;

    /** @var \Symfony\Component\VarDumper\Command\Descriptor\DumpDescriptorInterface[] */
    private $descriptors;

    /**
     * Create a new DumpServerCommand instance.
     */
    public function __construct(DumpServer $server, CliDumper $cliDumper, HtmlDumper $htmlDumper)
    {
        $this->server = $server;
        $this->descriptors = [
            'cli' => new CliDescriptor($cliDumper),
            'html' => new HtmlDescriptor($htmlDumper),
        ];

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $format = $this->option('format');
        $output = $this->getOutput();

        if (! $descriptor = $this->descriptors[$format] ?? null) {
            throw new RuntimeException(\sprintf('Unsupported format [%s].', $format));
        }

        $this->error('Symfony Var Dumper Server');

        $this->server->start();

        $output->success(\sprintf('Server listening on %s', $this->getHyperlink(\sprintf('http://%s', $this->server->getHost()))));

        $this->comment('Quit the server with CONTROL-C.');

        $this->server->listen(static function (Data $data, array $context, int $clientId) use ($descriptor, $output): void {
            $descriptor->describe($output, $data, $context, $clientId);
        });

        return 0;
    }
}
