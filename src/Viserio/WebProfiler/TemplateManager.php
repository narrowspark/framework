<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use ErrorException;
use ParseError;
use Throwable;
use TypeError;
use Viserio\Contracts\Support\Renderable as RenderableContract;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;

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
    private $templatePath = [];

    /**
     * Create a new template manager instance.
     *
     * @param array  $collectors
     * @param string $templatePath
     */
    public function __construct(array $collectors, string $templatePath)
    {
        $this->collectors = $collectors;
        $this->templatePath = $templatePath;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        $obLevel = ob_get_level();

        ob_start();

        extract(
            array_merge(
                $this->getSortedData(),
                [
                    'token' => '1'
                ]
            ),
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
            $message = 'Parse error: ' . $exception->getMessage();
            $severity = E_PARSE;
        } elseif ($exception instanceof TypeError) {
            $message = 'Type error: ' . $exception->getMessage();
            $severity = E_RECOVERABLE_ERROR;
        } else {
            $message = $exception->getMessage();
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

    public function getSortedData(): array
    {
        $data = [
            'tabs' => [],
            'panels' => [],
        ];

        foreach ($this->collectors as $name => $collector) {
            if ($collector instanceof TabAwareContract) {
                if ($collector instanceof TooltipAwareContract) {
                    $data['tabs'][] = [
                        'name' => $collector->getName(),
                        'tab' => $collector->getTab(),
                        'tooltip' => $collector->getTooltip(),
                        'position' => $collector->getTabPosition(),
                    ];
                } else {
                    $data['tabs'][] = [
                        'name' => $collector->getName(),
                        'tab' => $collector->getTab(),
                        'position' => $collector->getTabPosition(),
                    ];
                }
            }

            if ($collector instanceof PanelAwareContract) {
                $data['tabsRight'][]  = $collector->getPanel();
            }
        }

        return $data;
    }
}
