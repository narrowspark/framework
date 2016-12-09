<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Viserio\Contracts\WebProfiler\DataCollector as DataCollectorContract;

abstract class AbstractDataCollector implements DataCollectorContract
{
    /**
     * Array of all collected datas.
     *
     * @var array
     */
    protected $data;

    /**
     * {@inheritdoc}
     */
    public function getTabPosition(): string
    {
        return 'left';
    }

    /**
     * @param float $seconds
     *
     * @return string
     */
    protected function formatDuration(float $seconds): string
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'μs';
        } elseif ($seconds < 1) {
            return round($seconds * 1000, 2) . 'ms';
        }

        return round($seconds, 2) . 's';
    }

    /**
     * Convert a number string to bytes.
     *
     * @param string
     *
     * @return int
     */
    protected function convertToBytes(string $memoryLimit): int
    {
        if ($memoryLimit === '-1') {
            return -1;
        }

        $memoryLimit = strtolower($memoryLimit);
        $max = strtolower(ltrim($memoryLimit, '+'));

        if (0 === strpos($max, '0x')) {
            $max = intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = (int) $max;
        }

        switch (substr($memoryLimit, -1)) {
            case 't': $max *= 1024;
            case 'g': $max *= 1024;
            case 'm': $max *= 1024;
            case 'k': $max *= 1024;
        }

        return $max;
    }

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

    /**
     * Creates a tooltip group from array.
     *
     * @param array $data
     *
     * @return string
     */
    protected function createTooltipGroup(array $data): string
    {
        $tooltip = '<div class="webprofiler-tab-tooltip-group">';

        foreach ($data as $strong => $infos) {
            $tooltip .= '<div class="webprofiler-tab-tooltip-group-piece">';

            if (is_array($infos)) {
                $tooltip .= '<b>' . $strong . '</b>';

                foreach ($infos as $info) {
                    $tooltip .= '<span' . (isset($info['class']) ? ' class="' . $info['class'] . '"' : '') .'>' . $info['value'] . '</span>';
                }
            } else {
                $tooltip .= '<b>' . $strong . '</b><span>' . $infos . '</span>';
            }

            $tooltip .= '</div>';
        }

        $tooltip .= '</div>';

        return $tooltip;
    }
}
