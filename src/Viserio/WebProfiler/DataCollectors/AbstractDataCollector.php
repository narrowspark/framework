<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Viserio\Contracts\WebProfiler\DataCollector as DataCollectorContract;
use Viserio\WebProfiler\Util\TemplateHelper;
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
     * Add measurement to float time.
     *
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
     * Creates a tooltip group from array.
     *
     * array[]
     *     ['key']       string          Defines the name of <b> html element.
     *     ['value']     array|string    Defines the content to be shown in the <span> html element.
     *         ['class'] string          Adds a class to the <span> html element.
     *         ['value'] string          Adds the content.
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
     * array['id']     array     Defines witch tabs a shown.
     *     ['name']    string    Name of the tab
     *     ['content'] string    Tab content.
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

        foreach ($data as $key => $value) {
            $id = uniqid($key . '-');

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

    protected function createTable(array $data, string $name = null, array $headers = ['Key', 'Value'])
    {
        $html = $name ? '<h3>' . $name . '</h3>' : '';

        if (count($data) !== 0) {
            $html .= '<table><thead><tr>';

            foreach ($headers as $header) {
                $html .= '<th scope="col" class="' . $header . '">' . $header . '</th>';
            }

            $html .= '</tr></thead><tbody>';

            foreach ($data as $key => $values) {
                if (is_string($key)) {
                    $html .= '<tr>';
                    $html .= '<th>' . $key . '</th>';
                    $html .= '<td>' . TemplateHelper::dump($values) . '</td>';
                    $html .= '</tr>';
                } else {
                    $html .= '<tr>';

                    foreach ($values as $key => $value) {
                        $html .= '<td>' . TemplateHelper::dump($value) . '</td>';
                    }

                    $html .= '</tr>';
                }
            }

            $html .= '</tbody></table>';
        } else {
            $html .= '<div class="empty">Empty</div>';
        }

        return $html;
    }
}
