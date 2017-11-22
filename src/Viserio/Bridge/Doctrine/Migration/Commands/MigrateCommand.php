<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Commands;

use Doctrine\DBAL\Migrations\Configuration\Configuration;

class MigrateCommand extends AbstractCommand
{
    protected function outputHeader(Configuration $configuration)
    {
        $name = $configuration->getName();
        $name = $name ? $name : 'Doctrine Database Migrations';
        $name = str_repeat(' ', 20) . $name . str_repeat(' ', 20);

        $this->line(str_repeat(' ', mb_strlen($name)), 'question');
        $this->line($name, 'question');
        $this->line(str_repeat(' ', mb_strlen($name)), 'question');
        $this->line('');
    }
}
