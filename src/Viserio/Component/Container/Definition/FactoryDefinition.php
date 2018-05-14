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

use Viserio\Component\Container\Definition\Traits\ArgumentAwareTrait;
use Viserio\Component\Container\Definition\Traits\AutowiredAwareTrait;
use Viserio\Component\Container\Definition\Traits\ClassAwareTrait;
use Viserio\Component\Container\Definition\Traits\DecoratorAwareTrait;
use Viserio\Component\Container\Definition\Traits\FactoryAwareTrait;
use Viserio\Component\Container\Definition\Traits\MethodCallsAwareTrait;
use Viserio\Component\Container\Definition\Traits\PropertiesAwareTrait;
use Viserio\Component\Container\Definition\Traits\ReturnTypeTrait;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\BindingResolutionException;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

final class FactoryDefinition extends AbstractDefinition implements FactoryDefinitionContract
{
    use ClassAwareTrait;
    use PropertiesAwareTrait;
    use ArgumentAwareTrait;
    use DecoratorAwareTrait;
    use FactoryAwareTrait;
    use MethodCallsAwareTrait;
    use ReturnTypeTrait;
    use AutowiredAwareTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] service is deprecated. You should stop using it, as it will be removed in the future.';

    /**
     * Create a new Method Definition instance.
     *
     * @param string       $name
     * @param array|string $value
     * @param int          $type
     *
     * @throws \Viserio\Contract\Container\Exception\BindingResolutionException
     */
    public function __construct(string $name, $value, int $type)
    {
        parent::__construct($name, $type);

        $this->setValue($value);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Contract\Container\Exception\BindingResolutionException
     */
    public function setValue($value): DefinitionContract
    {
        [$class, $this->method] = self::splitFactory($value);

        $this->setClass($class);

        if ($this->method === '__construct') {
            throw new BindingResolutionException(\sprintf('Invalid factory method call for [%s] : [__construct] cannot be used as a method call.', $this->class));
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Returns a factory method split in class and method.
     *
     * @param array|string $method The formatting for the string looks like Class@Method or Class::Method
     *                             and for the array [[Class, 'Method'] or [new Class, 'Method']]
     *
     * @return array
     */
    public static function splitFactory($method): array
    {
        if (\is_string($method) && is_method($method)) {
            return \explode('@', $method, 2);
        }

        if (is_static_method($method)) {
            return \explode('::', $method, 2);
        }

        if (\is_array($method) && ((($method[0] instanceof ReferenceDefinition || $method[0] instanceof ObjectDefinitionContract || $method[0] instanceof FactoryDefinitionContract) && \is_string($method[1])) || \is_callable($method) || is_invokable($method[0]))) {
            return $method;
        }

        throw new InvalidArgumentException('No method found; The $method parameter must be of type string [Class@Method or Class::Method] or array [[Class, \'Method\'] or [new Class, \'Method\']].');
    }
}
