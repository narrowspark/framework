<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use ArrayAccess;
use Iterator;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;

class OptionsResolver extends AbstractOptionsResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve(?iterable $config = null, string $configId = null): iterable
    {
        $config     = $this->resolveOptions($config);
        $dimensions = $this->configClass->getDimensions();
        $dimensions = $dimensions instanceof Iterator ? iterator_to_array($dimensions) : $dimensions;

        if ($this->configClass instanceof RequiresConfigIdContract) {
            $dimensions[] = $configId;
        } elseif ($configId !== null) {
            throw new InvalidArgumentException(
                sprintf('The factory "%s" does not support multiple instances.', __CLASS__)
            );
        }

        // get configuration for provided dimensions
        foreach ($dimensions as $dimension) {
            if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
                throw UnexpectedValueException::invalidOptions($dimensions, $dimension);
            }

            if (! isset($config[$dimension])) {
                if (! $this->configClass instanceof RequiresMandatoryOptions && $this->configClass instanceof ProvidesDefaultOptions) {
                    break;
                }

                throw OptionNotFoundException::missingOptions($this, $dimension, $configId);
            }

            $config = $config[$dimension];
        }

        if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
            throw UnexpectedValueException::invalidOptions($this->configClass->getDimensions());
        }

        if ($this->configClass instanceof RequiresMandatoryOptions) {
            $this->checkMandatoryOptions($this->configClass->getMandatoryOptions(), $config);
        }

        if ($this->configClass instanceof ProvidesDefaultOptions) {
            $options = $this->configClass->getDefaultOptions();
            $config  = array_replace_recursive(
                $options instanceof Iterator ? iterator_to_array($options) : (array) $options,
                (array) $config
            );
        }

        return $config;
    }
}
