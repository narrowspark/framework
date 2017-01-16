<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;
use Viserio\Contracts\Config\Traits\ConfigAwareTrait;
use Viserio\Contracts\Config\Repository as RepositoryContract;

class Config extends Twig_Extension
{
    use ConfigAwareTrait;

    /**
     * Create a new config extension
     *
     * @param \Viserio\Contracts\Config\Repository
     */
    public function __construct(RepositoryContract $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Viserio_Bridge_Twig_Extension_Config';
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new Twig_SimpleFunction('config', [$this->config, 'get']),
            new Twig_SimpleFunction('config_get', [$this->config, 'get']),
            new Twig_SimpleFunction('config_has', [$this->config, 'has']),
        ];
    }
}
