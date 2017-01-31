<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extensions;

use Twig_Extension;
use Twig_Function;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Config\Traits\ConfigAwareTrait;

class ConfigExtension extends Twig_Extension
{
    use ConfigAwareTrait;

    /**
     * Create a new config extension.
     *
     * @param \Viserio\Component\Contracts\Config\Repository $config
     */
    public function __construct(RepositoryContract $config)
    {
        $this->options = $config;
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
            new Twig_Function('config', [$this->options, 'get']),
            new Twig_Function('config_get', [$this->options, 'get']),
            new Twig_Function('config_has', [$this->options, 'has']),
        ];
    }
}
