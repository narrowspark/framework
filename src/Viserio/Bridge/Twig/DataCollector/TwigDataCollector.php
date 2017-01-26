<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\DataCollector;

use Twig_Profiler_Profile;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
     * Create new files loaded collector instance.
     *
     * @param \Twig_Profiler_Profile $basePath
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
            'icon'  => 'ic_insert_drive_file_white_24px.svg',
            'label' => '',
            'value' => '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
    }
}
