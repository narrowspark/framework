<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig_Markup;
use Twig_Profiler_Dumper_Html;
use Twig_Profiler_Profile;
use Viserio\Component\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Component\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Component\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\WebProfiler\DataCollectors\AbstractDataCollector;

class TwigDataCollector extends AbstractDataCollector implements
    MenuAwareContract,
    PanelAwareContract,
    TooltipAwareContract
{
    /**
     * Twig profiler profile.
     *
     * @var \Twig_Profiler_Profile
     */
    private $profile;

    /**
     * Computed data.
     *
     * @var array
     */
    private $computed;

    /**
     * Create new twig collector instance.
     *
     * @param \Twig_Profiler_Profile $profile
     */
    public function __construct(Twig_Profiler_Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $this->data['profile'] = serialize($this->profile);
    }

    /**
     * Get twig profile.
     *
     * @return \Twig_Profiler_Profile
     */
    public function getProfile(): Twig_Profiler_Profile
    {
        if ($this->profile === null) {
            $this->profile = unserialize($this->data['profile']);
        }

        return $this->profile;
    }

    /**
     * Get duration time.
     *
     * @return int
     */
    public function getTime(): int
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
        return $this->getComputedData('template_count');
    }

    /**
     * Get counted templates.
     *
     * @return int
     */
    public function getTemplates(): int
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
        return $this->getComputedData('block_count');
    }

    /**
     * Get counted macros.
     *
     * @return int
     */
    public function getMacroCount(): int
    {
        return $this->getComputedData('macro_count');
    }

    /**
     * Get a html call graph.
     *
     * @return \Twig_Markup
     */
    public function getHtmlCallGraph()
    {
        $dumper = new Twig_Profiler_Dumper_Html();
        $dump   = $dumper->dump($this->getProfile());

        // needed to remove the hardcoded CSS styles
        $dump = str_replace([
            '<span style="background-color: #ffd">',
            '<span style="color: #d44">',
            '<span style="background-color: #dfd">',
        ], [
            '<span class="status-warning">',
            '<span class="status-error">',
            '<span class="status-success">',
        ], $dump);

        return new Twig_Markup($dump, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => file_get_contents(__DIR__ . '/../Resources/icons/ic_view_quilt_white_24px.svg'),
            'label' => 'Twig',
            'value' => $this->getComputedData('template_count'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        return $this->createTooltipGroup([
            'Profiler token'   => $this->getComputedData('template_count'),
            'Application name' => $this->getComputedData('block_count'),
            'Environment'      => $this->getComputedData('macro_count'),
        ]);
    }

    /**
     * Get computed data.
     *
     * @param string $index
     *
     * @return string|int
     */
    private function getComputedData(string $index)
    {
        if ($this->computed === null) {
            $this->computed = $this->generateComputeData($this->getProfile());
        }

        return $this->computed[$index];
    }

    /**
     * Generate Compute data.
     *
     * @param \Twig_Profiler_Profile $profile
     *
     * @return array
     */
    private function generateComputeData(Twig_Profiler_Profile $profile): array
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
