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

final class ArrayDefinition extends AbstractDefinition
{
    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] service is deprecated. You should stop using it, as it will be removed in the future.';

    /**
     * Create a new Array Definition instance.
     *
     * @param string $name
     * @param array  $value
     * @param int    $type
     */
    public function __construct(string $name, array $value, int $type)
    {
        parent::__construct($name, $type);

        $this->value = \array_map(function ($value) {
            if ($value instanceof AbstractDefinition) {
                return $value->getValue();
            }

            return $value;
        }, $value);
    }
}
