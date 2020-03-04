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

namespace Viserio\Component\Http\Stream;

use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Util;

class LazyOpenStream extends AbstractStreamDecorator
{
    /**
     * File name which should be lazily open.
     *
     * @var string
     */
    private $filename;

    /**
     * fopen mode to use when opening the stream.
     *
     * @var string
     */
    private $mode;

    /**
     * Create a new lazy open stream instance.
     *
     * @noinspection MagicMethodsValidityInspection
     */
    public function __construct(string $filename, string $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;
    }

    /**
     * {@inheritdoc}
     */
    protected function createStream(): StreamInterface
    {
        return new Stream(Util::tryFopen($this->filename, $this->mode));
    }
}
