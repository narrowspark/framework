<?php
declare(strict_types=1);
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
     * @var string|null
     */
    private $output;

    /**
     * Check for end of dump.
     *
     * @param int $line
     * @param int $depth
     */
    public function __invoke($line, $depth)
    {
        // A negative depth means "end of dump"
        if ($depth >= 0) {
            // Adds a two spaces indentation to the line
            $this->output .= str_repeat('  ', $depth) . $line . "\n";
        }
    }

    /**
     * Get the dump output.
     *
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * Clear the output.
     */
    public function flush(): void
    {
        $this->output = null;
    }
}
