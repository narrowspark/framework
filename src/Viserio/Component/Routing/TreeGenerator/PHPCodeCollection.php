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

namespace Viserio\Component\Routing\TreeGenerator;

final class PHPCodeCollection
{
    /**
     * The php code.
     *
     * @var string
     */
    public $code = '';

    /**
     * The current indentation level of the code.
     *
     * @var int
     */
    public $indent = '';

    /**
     * Appends the supplied code to the builder.
     */
    public function append(string $code): void
    {
        $indent = \str_repeat(' ', 4 * $this->indent);

        $this->code .= $indent . \str_replace("\n", "\n" . $indent, $code);
    }

    /**
     * Appends the supplied code and a new line to the builder.
     */
    public function appendLine(string $code = ''): void
    {
        $this->append($code);
        $this->code .= "\n";
    }
}
