<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
     *
     * @param string $name
     * @param mixed  $value
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
    public function setPublic(bool $bool): DefinitionContract
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
    public function setLazy(bool $bool): DefinitionContract
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
     *
     * @param mixed $value
     */
    private function checkValue($value): void
    {
        if (\is_array($value)) {
            foreach ($value as $v) {
                $this->checkValue($v);
            }

            return;
        }

        if ($value instanceof DefinitionContract || $value instanceof ArgumentContract || \is_object($value) || \is_callable($value)) {
            throw new InvalidArgumentException('You can´t register a ParameterDefinition with a not supported type, supported types are ["int", "float", "string", "bool", "array", "null", "iterable"].');
        }
    }
}
