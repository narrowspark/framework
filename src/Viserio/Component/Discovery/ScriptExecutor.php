<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\ProcessExecutor;

class ScriptExecutor
{
    private $composer;

    private $io;

    private $executor;

    private $manifest;

    public function __construct(Composer $composer, IOInterface $io, array $manifest, ProcessExecutor $executor = null)
    {
        $this->composer = $composer;
        $this->io       = $io;
        $this->manifest = $manifest;
        $this->executor = $executor ?: new ProcessExecutor();
    }
}
