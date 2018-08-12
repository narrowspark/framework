<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Automatic;

use Narrowspark\Automatic\Common\ScriptExtender\PhpScriptExtender;
use Viserio\Component\Console\Application;

class CerebroScriptExtender extends PhpScriptExtender
{
    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return 'cerebro-cmd';
    }

    /**
     * {@inheritdoc}
     */
    public function expand(string $cmd): string
    {
        $console = Application::cerebroBinary();

        if ($this->io->isDecorated()) {
            $console .= ' --ansi';
        }

        return parent::expand($console . ' ' . $cmd);
    }
}
