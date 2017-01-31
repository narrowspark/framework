<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Filters;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;
use Viserio\Component\Contracts\Exception\Filter as FilterContract;
use Viserio\Component\Support\Traits\ConfigureOptionsTrait;

class VerboseFilter implements FilterContract, RequiresConfig, RequiresMandatoryOptions
{
    use ConfigurationTrait;
    use ConfigureOptionsTrait;

    /**
     * Create a new verbose filter instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->configureOptions($container);
    }

    /**
     * {@inheritdoc}
     */
    public function dimensions(): iterable
    {
        return ['viserio', 'exception'];
    }

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions(): iterable
    {
        return ['debug'];
    }

    /**
     * {@inheritdoc}
     */
    public function filter(
        array $displayers,
        RequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        if ($this->options['debug'] !== true) {
            foreach ($displayers as $index => $displayer) {
                if ($displayer->isVerbose()) {
                    unset($displayers[$index]);
                }
            }
        }

        return array_values($displayers);
    }
}
