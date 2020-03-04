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

namespace Viserio\Component\Profiler\Util;

/**
 * Used as output callable for Symfony\Component\VarDumper\Dumper\HtmlDumper::dump().
 *
 * @see TemplateHelper::dump()
 */
final class HtmlDumperOutput
{
    /**
     * Output of the dump.
     *
     * @var null|string
     */
    private $output;

    /**
     * Check for end of dump.
     *
     * @param int $line
     * @param int $depth
     */
    public function __invoke($line, $depth): void
    {
        // A negative depth means "end of dump"
        if ($depth >= 0) {
            // Adds a two spaces indentation to the line
            $this->output .= \str_repeat('  ', $depth) . $line . "\n";
        }
    }

    /**
     * Get the dump output.
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * Clear the output.
     */
    public function reset(): void
    {
        $this->output = null;
    }
}
