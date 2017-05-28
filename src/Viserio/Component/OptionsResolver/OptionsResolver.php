<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use ArrayAccess;
use InvalidArgumentException;
use Iterator;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class OptionsResolver extends AbstractOptionsResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve(string $configId = null): array
    {
        $configClass = $this->configClass;
        $config      = $this->config;
        $dimensions  = [];

        if ($configClass instanceof RequiresComponentConfigContract) {
            $dimensions  = $configClass->getDimensions();
            $dimensions  = $dimensions instanceof Iterator ? iterator_to_array($dimensions) : $dimensions;
        }

        if ($configClass instanceof RequiresConfigIdContract ||
            $configClass instanceof RequiresComponentConfigIdContract
        ) {
            $dimensions[] = $configId;
        } elseif ($configId !== null) {
            throw new InvalidArgumentException(
                sprintf('The factory [%s] does not support multiple instances.', get_class($this->configClass))
            );
        }

        // get configuration for provided dimensions
        $config = $this->getConfigurationDimensions($dimensions, $config, $configClass, $configId);

        if ((array) $config !== $config && ! $config instanceof ArrayAccess && $configClass instanceof RequiresComponentConfigIdContract) {
            throw new UnexpectedValueException($configClass->getDimensions());
        }

        if ($configClass instanceof RequiresMandatoryOptionsContract) {
            $this->checkMandatoryOptions($configClass->getMandatoryOptions(), $config);
        }

        if ($configClass instanceof ProvidesDefaultOptionsContract) {
            $options = $configClass->getDefaultOptions();
            $config  = array_replace_recursive(
                $options instanceof Iterator ? iterator_to_array($options) : (array) $options,
                (array) $config
            );
        }

        return (array) $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveConfiguration($data)
    {
        if (is_iterable($data)) {
            return $data;
        } elseif ($data instanceof ContainerInterface) {
            if ($data->has(RepositoryContract::class)) {
                return $data->get(RepositoryContract::class);
            } elseif ($data->has('config')) {
                return $data->get('config');
            } elseif ($data->has('options')) {
                return $data->get('options');
            }
        }

        throw new RuntimeException('No configuration found.');
    }
}
