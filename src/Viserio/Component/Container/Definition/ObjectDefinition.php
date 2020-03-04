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

use PhpParser\PrettyPrinter\Standard;
use stdClass;
use Viserio\Component\Container\Definition\Traits\ArgumentAwareTrait;
use Viserio\Component\Container\Definition\Traits\AutowiredAwareTrait;
use Viserio\Component\Container\Definition\Traits\ClassAwareTrait;
use Viserio\Component\Container\Definition\Traits\DecoratorAwareTrait;
use Viserio\Component\Container\Definition\Traits\MethodCallsAwareTrait;
use Viserio\Component\Container\Definition\Traits\PropertiesAwareTrait;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Support\Exception\MissingPackageException;

final class ObjectDefinition extends AbstractDefinition implements ObjectDefinitionContract
{
    use ArgumentAwareTrait;
    use ClassAwareTrait;
    use DecoratorAwareTrait;
    use MethodCallsAwareTrait;
    use PropertiesAwareTrait;
    use AutowiredAwareTrait;

    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] service is deprecated. You should stop using it, as it will be removed in the future.';

    /**
     * Create a new Object Definition instance.
     *
     * @param object|string $value
     *
     * @throws \Viserio\Contract\Support\Exception\MissingPackageException
     */
    public function __construct(string $name, $value, int $type)
    {
        parent::__construct($name, $type);

        if (is_anonymous_class($value) && ! \class_exists(Standard::class)) {
            throw new MissingPackageException(['nikic/php-parser'], self::class, ', anonymous object dumping');
        }

        $this->value = $value;
        $this->setClass($value);

        if ($this->class === stdClass::class && \is_object($value)) {
            $properties = [];

            foreach ((array) $value as $key => $v) {
                $properties[$key] = [$v, false];
            }

            $this->setProperties($properties);
        }
    }
}
