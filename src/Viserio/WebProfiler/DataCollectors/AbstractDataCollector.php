<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Viserio\Contracts\WebProfiler\DataCollector as DataCollectorContract;
use Viserio\Support\Str;

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
    public function getMenuPosition(): string
    {
        return 'left';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        $namespace = substr(get_called_class(), 0, strrpos(get_called_class(), '\\'));

        return Str::snake(str_replace($namespace . '\\', '', get_class($this)), '-');
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
            case 't':
                $max *= 1024;
            case 'g':
                $max *= 1024;
            case 'm':
                $max *= 1024;
            case 'k':
                $max *= 1024;
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
                    $output .= str_repeat('  ', $depth) . $line . "\n";
                }
            }
        );

        return trim($output);
    }

    /**
     * Creates a tooltip group from array.
     *
     * array[]
     *          ['key']           string          Defines the name of <b> html element.
     *          ['value']         array|string    Defines the content to be shown in the <span> html element.
     *              ['class']     string          Adds a class to the <span> html element.
     *              ['value']     string          Adds the content.
     *
     * @param array $data (See above)
     *
     * @return string
     */
    protected function createTooltipGroup(array $data): string
    {
        $tooltip = '<div class="webprofiler-menu-tooltip-group">';

        foreach ($data as $strong => $infos) {
            $tooltip .= '<div class="webprofiler-menu-tooltip-group-piece">';

            if (is_array($infos)) {
                $tooltip .= '<b>' . $strong . '</b>';

                foreach ($infos as $info) {
                    $tooltip .= '<span' . (isset($info['class']) ? ' class="' . $info['class'] . '"' : '') . '>' . $info['value'] . '</span>';
                }
            } else {
                $tooltip .= '<b>' . $strong . '</b><span>' . $infos . '</span>';
            }

            $tooltip .= '</div>';
        }

        $tooltip .= '</div>';

        return $tooltip;
    }

    /**
     * Creates a tab slider.
     *
     *  array['id']            array     Defines witch tabs a shown.
     *          ['name']       string    Name of the tab
     *          ['content']    string    Tab content.
     *
     * @param array $data (See above)
     *
     * @return string
     */
    protected function createTabs(array $data): string
    {
        $grid = '';

        if (($counted = count($data)) < 12) {
            $grid = ' col span_' . (12 / $counted);
        }

        $html = '<div class="webprofiler-tabs' . ($grid !== '' ? ' row' : '') . '">';
        $checked = false;

        foreach ($data as $id => $value) {
            $html .= '<div class="webprofiler-tabs-tab' . $grid . '">';
            $html .= '<input type="radio" name="tabgroup" id="tab-' . $id . '"' . ($checked !== 'checked' ? $checked = 'checked' : '') . '>';
            $html .= '<label for="tab-' . $id . '">' . $value['name'] . '</label>';
            $html .= '<div class="webprofiler-tabs-tab-content">';
            $html .= $value['content'];
            $html .= '</div></div>';
        }

        $html .= '</div>';

        return $html;
    }

    protected function createTable(array $data, string $name = null, array $header = null)
    {
        $html = $name ? '<h3>' . $name . '</h3>' : '';

        if (count($data) !== 0) {
            $html .= '<table><thead><tr>';
            $html .= '<th scope="col" class="key">' . (isset($header['key']) ? $header['key'] : 'Key') . '</th>';
            $html .= '<th scope="col">' . (isset($header['key']) ? $header['value'] : 'Value') . '</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($data as $key => $value) {
                $html .= '<tr>';
                $html .= '<th>' . $key . '</th>';
                $html .= '<td>' . $this->formatVar($value) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        } else {
            $html .= '<div class="empty">Empty</div>';
        }

        return $html;
    }
}
