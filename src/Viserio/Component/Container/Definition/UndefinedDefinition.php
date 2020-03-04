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

use Viserio\Component\Container\Definition\Traits\ArgumentAwareTrait;
use Viserio\Component\Container\Definition\Traits\AutowiredAwareTrait;
use Viserio\Component\Container\Definition\Traits\ClassAwareTrait;
use Viserio\Component\Container\Definition\Traits\DecoratorAwareTrait;
use Viserio\Component\Container\Definition\Traits\FactoryAwareTrait;
use Viserio\Component\Container\Definition\Traits\MethodCallsAwareTrait;
use Viserio\Component\Container\Definition\Traits\PropertiesAwareTrait;
use Viserio\Component\Container\Definition\Traits\ReturnTypeTrait;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;

final class UndefinedDefinition extends AbstractDefinition implements UndefinedDefinitionContract
{
    use ArgumentAwareTrait;
    use ClassAwareTrait;
    use DecoratorAwareTrait;
    use MethodCallsAwareTrait;
    use PropertiesAwareTrait;
    use FactoryAwareTrait;
    use ReturnTypeTrait;
    use AutowiredAwareTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] service is deprecated. You should stop using it, as it will be removed in the future.';

    /**
     * Create a new Undefined Definition instance.
     *
     * @param object|string $value
     *
     * @throws \Viserio\Contract\Support\Exception\MissingPackageException
     */
    public function __construct(string $name, $value, int $type)
    {
        parent::__construct($name, $type);

        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function isAutowired(): bool
    {
        return $this->autowired && $this->class !== null;
    }
}
