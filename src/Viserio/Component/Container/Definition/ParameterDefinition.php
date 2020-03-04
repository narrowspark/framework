<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Container\Definition;

use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

final class ParameterDefinition extends AbstractDefinition
{
    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] parameter is deprecated. You should stop using it, as it will be removed in the future.';

    /**
     * Create a new parameter definition instance.
     */
    public function __construct(string $name, $value)
    {
        parent::__construct($name, 0);

        $this->setValue($value);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value): DefinitionContract
    {
        $this->checkValue($value);

        return parent::setValue($value);
    }

    /**
     * Parameters are always public.
     *
     * {@inheritdoc}
     */
    public function setPublic(bool $bool)
    {
        // cant be changed

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublic(): bool
    {
        return true;
    }

    /**
     * Parameters are never lazy.
     *
     * {@inheritdoc}
     */
    public function setLazy(bool $bool)
    {
        // cant be changed

        return $this;
    }

    /**
     * Parameters are always shared.
     *
     * {@inheritdoc}
     */
    public function isShared(): bool
    {
        return true;
    }

    /**
     * Check if correct value is given.
     */
    private function checkValue($value): void
    {
        if (\is_array($value)) {
            foreach ($value as $v) {
                $this->checkValue($v);
            }

            return;
        }

        if (! \is_string($value) && ($value instanceof DefinitionContract || $value instanceof ArgumentContract || \is_object($value) || \is_callable($value))) {
            throw new InvalidArgumentException(\sprintf('You can´t register a ParameterDefinition [%s] with a not supported type [%s], supported types are ["int", "integer", "float", "string", "bool", "boolean", "array", "null", "array"].', $this->getName(), \is_object($value) ? \get_class($value) : \gettype($value)));
        }
    }
}
