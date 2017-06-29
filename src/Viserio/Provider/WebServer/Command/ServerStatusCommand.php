<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer\Command;

use Viserio\Component\Console\Command\Command;

class ServerStatusCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'server:status';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Starts a local web server in the background.';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {

    }
}
