<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Filters;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Contracts\Exception\Filter as FilterContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class VerboseFilter implements FilterContract, RequiresComponentConfigContract, RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * Create a new verbose filter instance.
     *
     * @param \Psr\Container\ContainerInterface|iterable $data
     */
    public function __construct($data)
    {
        $this->configureOptions($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'exception'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return ['debug'];
    }

    /**
     * {@inheritdoc}
     */
    public function filter(
        array $displayers,
        ServerRequestInterface $request,
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
