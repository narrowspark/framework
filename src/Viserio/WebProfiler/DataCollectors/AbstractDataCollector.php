<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Viserio\Contracts\WebProfiler\DataCollector as DataCollectorContract;

abstract class AbstractDataCollector implements DataCollectorContract
{
    /**
     * [formatVar description]
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function formatVar($data): string
    {
        $output = '';
        $dumper = new CliDumper();
        $cloner = new VarCloner();

        $dumper->dump(
            $cloner->cloneVar($data),
            function ($line, $depth) use (&$output) {
                // A negative depth means "end of dump"
                if ($depth >= 0) {
                    // Adds a two spaces indentation to the line
                    $output .= str_repeat('  ', $depth).$line."\n";
                }
            }
        );

        return trim($output);
    }
}
