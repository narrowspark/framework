<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use ArrayAccess;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Iterator;
use RuntimeException;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ComponentOptionsResolver extends AbstractOptionsResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve(string $configId = null): array
    {
        $configClass = $this->configClass;
        $config      = $this->config;
        $dimensions  = $configClass->getDimensions();
        $dimensions  = $dimensions instanceof Iterator ? iterator_to_array($dimensions) : $dimensions;

        if ($configClass instanceof RequiresConfigIdContract) {
            $dimensions[] = $configId;
        } elseif ($configId !== null) {
            throw new InvalidArgumentException(
                sprintf('The factory "%s" does not support multiple instances.', __CLASS__)
            );
        }

        // get configuration for provided dimensions
        foreach ($dimensions as $dimension) {
            if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
                throw new UnexpectedValueException($dimensions, $dimension);
            }

            if (! isset($config[$dimension])) {
                if (! $configClass instanceof RequiresMandatoryOptionsContract &&
                    $configClass instanceof ProvidesDefaultOptionsContract
                ) {
                    break;
                }

                throw OptionNotFoundException::missingOptions($this, $dimension, $configId);
            }

            $config = $config[$dimension];
        }

        if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
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

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveConfiguration($data)
    {
        if ($data instanceof ContainerInterface) {
            if ($data->has(RepositoryContract::class)) {
                return $data->get(RepositoryContract::class);
            } elseif ($data->has('config')) {
                return $data->get('config');
            } elseif ($data->has('options')) {
                return $data->get('options');
            }
        } elseif (is_iterable($data)) {
            return $data;
        }

        throw new RuntimeException('No configuration found.');
    }
}
