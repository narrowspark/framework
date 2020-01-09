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

use Traversable;

final class IteratorDefinition extends AbstractDefinition
{
    /**
     * Default deprecation template.
     *
     * @var string
     */
    protected $defaultDeprecationTemplate = 'The [%s] service is deprecated. You should stop using it, as it will be removed in the future.';

    /**
     * Create a new Iterator Definition instance.
     *
     * @param string      $name
     * @param Traversable $value
     * @param int         $type
     */
    public function __construct(string $name, Traversable $value, int $type)
    {
        parent::__construct($name, $type);

        $this->value = $value;
    }
}
