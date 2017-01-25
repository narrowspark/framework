<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Commands;

use Viserio\Component\Console\Command\Command;

class DebugCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'twig:debug';
}
