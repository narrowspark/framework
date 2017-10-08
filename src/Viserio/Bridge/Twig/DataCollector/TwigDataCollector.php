<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Markup;
use Twig\Profiler\Dumper\HtmlDumper;
use Twig\Profiler\Profile;
use Viserio\Component\Contract\Profiler\AssetAware as AssetAwareContract;
use Viserio\Component\Contract\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Contract\Profiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;

class TwigDataCollector extends AbstractDataCollector implements
    PanelAwareContract,
    AssetAwareContract,
    TooltipAwareContract
{
    /**
     * Twig profiler profile.
     *
     * @var \Twig\Profiler\Profile
     */
    private $profile;

    /**
     * Twig environment.
     *
     * @var \Twig\Environment
     */
    private $twigEnvironment;

    /**
     * Computed data.
     *
     * @var array
     */
    private $computed = [];

    /**
     * Create new twig collector instance.
     *
     * @param \Twig\Profiler\Profile $profile
     * @param \Twig\Environment      $twigEnvironment
     */
    public function __construct(Profile $profile, Environment $twigEnvironment)
    {
        $this->profile         = $profile;
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $this->data['template_paths'] = [];

        $templateFinder = function (Profile $profile) use (&$templateFinder) {
            if ($profile->isTemplate() &&
                $template = $this->twigEnvironment->load($profile->getName())->getSourceContext()->getPath()
            ) {
                $this->data['template_paths'][$profile->getName()] = $template;
            }

            foreach ($profile as $p) {
                $templateFinder($p);
            }
        };

        $templateFinder($this->profile);
    }

    /**
     * Get twig profile.
     *
     * @return \Twig\Profiler\Profile
     */
    public function getProfile(): Profile
    {
        return $this->profile;
    }
    /**
     * Get duration time.
     *
     * @return float
     */
    public function getTime(): float
    {
        return $this->getProfile()->getDuration() * 1000;
    }

    /**
     * Get counted templates.
     *
     * @return int
     */
    public function getTemplateCount(): int
    {
        return (int) $this->getComputedData('template_count');
    }

    /**
     * Get counted templates.
     *
     * @return array
     */
    public function getTemplates(): array
    {
        return $this->getComputedData('templates');
    }

    /**
     * Get counted blocks.
     *
     * @return int
     */
    public function getBlockCount(): int
    {
        return (int) $this->getComputedData('block_count');
    }

    /**
     * Get counted macros.
     *
     * @return int
     */
    public function getMacroCount(): int
    {
        return (int) $this->getComputedData('macro_count');
    }

    /**
     * List of twig file paths.
     *
     * @return array
     */
    public function getTemplatePaths()
    {
        return $this->data['template_paths'];
    }

    /**
     * Get a html call graph.
     *
     * @return \Twig\Markup
     *
     * @codeCoverageIgnore
     */
    public function getHtmlCallGraph(): Markup
    {
        $dumper = new HtmlDumper();
        $dump   = $dumper->dump($this->getProfile());

        // needed to remove the hardcoded CSS styles
        $dump = \str_replace([
            '<span style="background-color: #ffd">',
            '<span style="color: #d44">',
            '<span style="background-color: #dfd">',
        ], [
            '<span class="status-warning">',
            '<span class="status-error">',
            '<span class="status-success">',
        ], $dump);

        return new Markup($dump, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getAssets(): array
    {
        return [
            'css' => __DIR__ . '/Resources/css/twig.css',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $data     = [];
        $twigHtml = $this->createMetrics(
            [
                'Render time'    => $this->formatDuration($this->getTime()),
                'Template calls' => $this->getTemplateCount(),
                'Block calls'    => $this->getBlockCount(),
                'Macro calls'    => $this->getMacroCount(),
            ],
            'Twig Metrics'
        );

        $templates = [];

        foreach ($this->getTemplates() as $template => $count) {
            $templates[$template] = $count;
        }

        $twigHtml .= $this->createTable(
            $templates,
            [
                'name'      => 'Rendered Templates',
                'headers'   => ['Template Name', 'Render Count'],
                'vardumper' => false,
            ]
        );

        $twigHtml .= '<div class="twig-graph"><h3>Rendering Call Graph</h3>';
        $twigHtml .= $this->getHtmlCallGraph();
        $twigHtml .= '</div>';
        $extensions = $this->twigEnvironment->getExtensions();
        $data[]     = ['name' => 'Twig <span class="counter">' . $this->getTemplateCount() . '</span>', 'content' => $twigHtml];
        $data[]     = ['name' => 'Twig Extensions <span class="counter">' . \count($extensions) . '</span>', 'content' => $this->createTable(
            \array_keys($extensions),
            ['headers' => ['Extension'], 'vardumper' => false]
        )];

        return $this->createTabs($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => \file_get_contents(__DIR__ . '/Resources/icons/ic_view_quilt_white_24px.svg'),
            'label' => 'Twig',
            'value' => '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        return $this->createTooltipGroup([
            'Template calls' => $this->getComputedData('template_count'),
            'Block calls'    => $this->getComputedData('block_count'),
            'Macro calls'    => $this->getComputedData('macro_count'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->profile->reset();

        $this->computed = [];
        $this->data     = [];
    }

    /**
     * Get computed data.
     *
     * @param string $index
     *
     * @return mixed
     */
    private function getComputedData(string $index)
    {
        if (count($this->computed) === 0) {
            $this->computed = $this->generateComputeData($this->getProfile());
        }

        return $this->computed[$index];
    }

    /**
     * Generate Compute data.
     *
     * @param \Twig\Profiler\Profile $profile
     *
     * @return array
     */
    private function generateComputeData(Profile $profile): array
    {
        $data = [
            'template_count' => 0,
            'block_count'    => 0,
            'macro_count'    => 0,
        ];

        $templates = [];

        foreach ($profile as $p) {
            $d = $this->generateComputeData($p);

            $data['template_count'] += ($p->isTemplate() ? 1 : 0) + $d['template_count'];
            $data['block_count'] += ($p->isBlock() ? 1 : 0) + $d['block_count'];
            $data['macro_count'] += ($p->isMacro() ? 1 : 0) + $d['macro_count'];

            if ($p->isTemplate()) {
                if (! isset($templates[$p->getTemplate()])) {
                    $templates[$p->getTemplate()] = 1;
                } else {
                    ++$templates[$p->getTemplate()];
                }
            }

            foreach ($d['templates'] as $template => $count) {
                if (! isset($templates[$template])) {
                    $templates[$template] = $count;
                } else {
                    $templates[$template] += $count;
                }
            }
        }

        $data['templates'] = $templates;

        return $data;
    }
}
