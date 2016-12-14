<?php
declare(strict_types=1);
namespace Viserio\Foundation\Commands;

use Viserio\Console\Command\Command;

class KeyGenerateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'key';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Set the application key';
}
