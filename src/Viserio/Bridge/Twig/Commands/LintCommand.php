<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Commands;

use Viserio\Component\Console\Command\Command;

class LintCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'twig:lint';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Lints Twig templates';
}
