<?php
declare(strict_types=1);
namespace Viserio\Console\Tests\Fixture;

use Symfony\Component\Console\Output\{
    Output,
    OutputInterface
};

class SpyOutput extends Output implements OutputInterface
{
    public $output;

    protected function doWrite($message, $newline)
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
