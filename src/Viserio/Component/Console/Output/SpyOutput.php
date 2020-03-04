<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

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
