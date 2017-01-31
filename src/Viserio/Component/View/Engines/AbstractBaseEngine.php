<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engines;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\View\Engine as EngineContract;
use Viserio\Component\Support\Traits\ConfigureOptionsTrait;

abstract class AbstractBaseEngine implements EngineContract, RequiresConfig, RequiresMandatoryOptions
{
    use ConfigurationTrait;
    use ConfigureOptionsTrait;

    /**
     * Container instance.
     *
     * @var \Interop\Container\ContainerInterface|null
     */
    protected $container;

    /**
     * Create a new engine instance.
     *
     * @param \Interop\Container\ContainerInterface|null $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container  = $container;

        if ($container !== null) {
            $this->configureOptions($container);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions(): iterable
    {
        return [
            'paths',
            'engines',
        ];
    }
}
