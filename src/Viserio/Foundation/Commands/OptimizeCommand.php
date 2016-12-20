<?php
declare(strict_types=1);
namespace Viserio\Foundation\Commands;

use Viserio\Console\Command\Command;

class OptimizeCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'optimize';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Optimize the framework for better performance';
}
