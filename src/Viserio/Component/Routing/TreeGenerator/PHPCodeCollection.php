<?php
declare(strict_types=1);
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

        $this->code .= $indent . \str_replace(PHP_EOL, PHP_EOL . $indent, $code);
    }

    /**
     * Appends the supplied code and a new line to the builder.
     *
     * @param string $code
     */
    public function appendLine(string $code = ''): void
    {
        $this->append($code);
        $this->code .= PHP_EOL;
    }
}
