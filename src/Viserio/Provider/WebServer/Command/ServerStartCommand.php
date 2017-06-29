<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer\Command;

use Viserio\Component\Console\Command\Command;

class ServerStartCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'server:start';

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
