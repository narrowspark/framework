<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Fixture;

use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class SpyOutput extends Output implements OutputInterface
{
    public $output;

    protected function doWrite($message, $newline): void
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
