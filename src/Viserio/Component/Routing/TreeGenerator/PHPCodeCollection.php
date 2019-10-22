<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
     *
     * @param string $code
     */
    public function append(string $code): void
    {
        $indent = \str_repeat(' ', 4 * $this->indent);

        $this->code .= $indent . \str_replace("\n", "\n" . $indent, $code);
    }

    /**
     * Appends the supplied code and a new line to the builder.
     *
     * @param string $code
     */
    public function appendLine(string $code = ''): void
    {
        $this->append($code);
        $this->code .= "\n";
    }
}
