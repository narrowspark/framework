<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer\Command;

use Viserio\Component\Console\Command\Command;

class ServerRunCommand extends Command
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
    }
}
