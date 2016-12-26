<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use ErrorException;
use ParseError;
use Throwable;
use TypeError;
use Viserio\Contracts\Support\Renderable as RenderableContract;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;

class TemplateManager implements RenderableContract
{
    /**
     * All registered data collectors.
     *
     * @var array
     */
    protected $collectors = [];

    /**
     * Path for the webprofiler template.
     *
     * @var string
     */
    private $templatePath;

    /**
     * List of icons.
     *
     * @var array
     */
    private $icons = [];

    /**
     * Request token.
     *
     * @var string
     */
    private $token = '';

    /**
     * Create a new template manager instance.
     *
     * @param array  $collectors
     * @param string $templatePath
     * @param string $token
     * @param array  $icons
     */
    public function __construct(
        array $collectors,
        string $templatePath,
        string $token,
        array $icons = []
    ) {
        $this->collectors   = $collectors;
        $this->templatePath = $templatePath;
        $this->token        = $token;
        $this->icons        = $icons;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        $obLevel = ob_get_level();

        ob_start();
        $data = array_merge(
            $this->getSortedData(),
            [
                'token' => $this->token,
            ]
        );

        extract(
            $data,
            EXTR_PREFIX_SAME,
            'viserio'
        );

        try {
            require $this->templatePath;
        } catch (Throwable $exception) {
            $this->handleViewException(
                $this->getErrorException($exception),
                $obLevel
            );
        }

        // @codeCoverageIgnoreStart
        // Return temporary output buffer content, destroy output buffer
        return ltrim(ob_get_clean());
        // @codeCoverageIgnoreEnd
    }

    /**
     * Sort all datas from collectors.
     *
     * @return array
     */
    public function getSortedData(): array
    {
        $data = [
            'menus'  => [],
            'panels' => [],
            'icons'  => $this->icons,
        ];

        foreach ($this->collectors as $name => $collector) {
            if ($collector instanceof MenuAwareContract) {
                if ($collector instanceof TooltipAwareContract) {
                    $data['menus'][$collector->getName()] = [
                        'menu'     => $collector->getMenu(),
                        'tooltip'  => $collector->getTooltip(),
                        'position' => $collector->getMenuPosition(),
                    ];
                } else {
                    $data['menus'][$collector->getName()] = [
                        'menu'     => $collector->getMenu(),
                        'position' => $collector->getMenuPosition(),
                    ];
                }
            }

            if ($collector instanceof PanelAwareContract) {
                $class = '';
                $panel = $collector->getPanel();

                if (mb_strpos($panel, '<div class="webprofiler-tabs') !== false) {
                    $class = ' webprofiler-body-has-tabs';
                } elseif (mb_strpos($panel, '<select class="content-selector"') !== false) {
                    $class = ' webprofiler-body-has-selector';
                } elseif (mb_strpos($panel, '<table class="row">') !== false) {
                    $class = ' webprofiler-body-has-table';
                }

                $data['panels'][$collector->getName()] = [
                    'content' => $panel,
                    'class'   => $class,
                ];
            }
        }

        return $data;
    }

    /**
     * Handle a view exception.
     *
     * @param \Throwable $exception
     * @param int        $obLevel
     *
     * @throws \Throwable
     */
    protected function handleViewException(Throwable $exception, int $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }

        throw $exception;
    }

    /**
     * Get a ErrorException instance.
     *
     * @param \ParseError|\TypeError|\Throwable $exception
     *
     * @return \ErrorException
     */
    private function getErrorException($exception): ErrorException
    {
        // @codeCoverageIgnoreStart
        if ($exception instanceof ParseError) {
            $message  = 'Parse error: ' . $exception->getMessage();
            $severity = E_PARSE;
        } elseif ($exception instanceof TypeError) {
            $message  = 'Type error: ' . $exception->getMessage();
            $severity = E_RECOVERABLE_ERROR;
        } else {
            $message  = $exception->getMessage();
            $severity = E_ERROR;
        }
        // @codeCoverageIgnoreEnd

        return new ErrorException(
            $message,
            $exception->getCode(),
            $severity,
            $exception->getFile(),
            $exception->getLine()
        );
    }
}
