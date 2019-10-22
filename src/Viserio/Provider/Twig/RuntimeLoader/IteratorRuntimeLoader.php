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

namespace Viserio\Provider\Twig\RuntimeLoader;

use Twig\RuntimeLoader\RuntimeLoaderInterface;

class IteratorRuntimeLoader implements RuntimeLoaderInterface
{
    /** @var \IteratorAggregate */
    private $iterator;

    /**
     * @param \IteratorAggregate $iterator Indexed by command names
     */
    public function __construct(\IteratorAggregate $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function load($class)
    {
        foreach ($this->iterator as $id => $value) {
            if ($id === $class) {
                return $value;
            }
        }

        return null;
    }
}
