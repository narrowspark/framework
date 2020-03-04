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
