<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Util;

/**
 * Used as output callable for Symfony\Component\VarDumper\Dumper\HtmlDumper::dump()
 *
 * @see TemplateHelper::dump()
 */
class HtmlDumperOutput
{
    /**
     * [$output description]
     *
     * @var string|null
     */
    private $output;

    /**
     * [__invoke description]
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
     * [getOutput description]
     *
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * [clear description]
     */
    public function clear(): void
    {
        $this->output = null;
    }
}
