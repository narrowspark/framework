<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Config\Traits\ConfigAwareTrait;

class ConfigExtension extends AbstractExtension
{
    use ConfigAwareTrait;

    /**
     * Create a new config extension.
     *
     * @param \Viserio\Component\Contract\Config\Repository $config
     */
    public function __construct(RepositoryContract $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Viserio_Bridge_Twig_Extension_Config';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', [$this->config, 'get']),
            new TwigFunction('config_get', [$this->config, 'get']),
            new TwigFunction('config_has', [$this->config, 'has']),
        ];
    }
}
