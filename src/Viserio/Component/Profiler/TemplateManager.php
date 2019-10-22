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

namespace Viserio\Component\Profiler;

use Viserio\Contract\Profiler\PanelAware as PanelAwareContract;
use Viserio\Contract\Profiler\TooltipAware as TooltipAwareContract;
use Viserio\Contract\Support\Renderable as RenderableContract;

class TemplateManager implements RenderableContract
{
    /**
     * All registered data collectors.
     *
     * @var array
     */
    protected $collectors = [];

    /**
     * Path for the Profiler template.
     *
     * @var string
     */
    private $templatePath;

    /**
     * List of icons.
     *
     * @var array
     */
    private $icons;

    /**
     * Request token.
     *
     * @var string
     */
    private $token;

    /**
     * Create a new template manager instance.
     *
     * @param array  $collectors
     * @param string $templatePath
     * @param string $token
     * @param array  $icons
     */
    public function __construct(array $collectors, string $templatePath, string $token, array $icons = [])
    {
        $this->collectors = $collectors;
        $this->templatePath = $templatePath;
        $this->token = $token;
        $this->icons = $icons;
    }

    /**
     * Escapes a string for output in an HTML document.
     *
     * @param string $raw
     *
     * @return string
     */
    public static function escape(string $raw): string
    {
        $flags = \ENT_QUOTES;

        if (\defined('ENT_SUBSTITUTE')) {
            $flags |= \ENT_SUBSTITUTE;
        }

        $raw = \str_replace(\chr(9), '    ', $raw);

        return \htmlspecialchars($raw, $flags);
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        $obLevel = \ob_get_level();

        \ob_start();

        $data = \array_merge(
            $this->getSortedData(),
            [
                'token' => $this->token,
            ]
        );

        \extract(
            $data,
            \EXTR_PREFIX_SAME,
            'viserio'
        );

        require $this->templatePath;

        /**
         * @codeCoverageIgnoreStart
         * Return temporary output buffer content, destroy output buffer
         */
        return \ltrim(\ob_get_clean());
        /** @codeCoverageIgnoreEnd */
    }

    /**
     * Sort all datas from collectors.
     *
     * @return array
     */
    public function getSortedData(): array
    {
        $data = [
            'menus' => [],
            'panels' => [],
            'icons' => $this->icons,
        ];

        foreach ($this->collectors as $name => $collector) {
            $collector = $collector['collector'];

            if ($collector instanceof TooltipAwareContract) {
                $data['menus'][$collector->getName()] = [
                    'menu' => $collector->getMenu(),
                    'tooltip' => $collector->getTooltip(),
                    'position' => $collector->getMenuPosition(),
                ];
            } else {
                $data['menus'][$collector->getName()] = [
                    'menu' => $collector->getMenu(),
                    'position' => $collector->getMenuPosition(),
                ];
            }

            if ($collector instanceof PanelAwareContract) {
                $class = '';
                $panel = $collector->getPanel();

                /** @codeCoverageIgnoreStart */
                if (\strpos($panel, '<div class="profiler-tabs') !== false) {
                    $class = ' profiler-body-has-tabs';
                } elseif (\strpos($panel, '<select class="content-selector"') !== false) {
                    $class = ' profiler-body-has-selector';
                } elseif (\strpos($panel, '<ul class="metrics"') !== false) {
                    $class = ' profiler-body-has-metrics';
                } elseif (\strpos($panel, '<table>') !== false) {
                    $class = ' profiler-body-has-table';
                }
                /** @codeCoverageIgnoreEnd */
                $data['panels'][$collector->getName()] = [
                    'content' => $panel,
                    'class' => $class,
                ];
            }
        }

        return $data;
    }
}
