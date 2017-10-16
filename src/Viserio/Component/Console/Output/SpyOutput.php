<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Output;

use Symfony\Component\Console\Output\Output;

class SpyOutput extends Output
{
    /**
     * Get the outputted string.
     *
     * @var string
     */
    public $output = '';

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline): void
    {
        $this->output .= $message . ($newline ? "\n" : '');
    }
}
