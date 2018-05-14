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
     * @param string        $name
     * @param object|string $value
     * @param int           $type
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
