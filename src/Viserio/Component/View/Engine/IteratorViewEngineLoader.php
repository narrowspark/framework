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

namespace Viserio\Component\View\Engine;

use IteratorAggregate;
use Viserio\Contract\View\Engine as EngineContract;
use Viserio\Contract\View\EngineResolver as EngineResolverContract;
use Viserio\Contract\View\Exception\ViewEngineNotFoundException;

/**
 * A simple view engine loader using generators to instantiate engines lazily.
 */
class IteratorViewEngineLoader implements EngineResolverContract
{
    /** @var IteratorAggregate */
    private $iterator;

    /**
     * @param IteratorAggregate $iterator Indexed by command names
     */
    public function __construct(IteratorAggregate $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, EngineContract $engine): void
    {
        $this->iterator[$name] = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $name): bool
    {
        foreach ($this->iterator as $id => $value) {
            if ($id === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name): EngineContract
    {
        foreach ($this->iterator as $id => $value) {
            if ($id === $name) {
                return $value;
            }
        }

        throw new ViewEngineNotFoundException(\sprintf('View engine [%s] does not exist.', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function getNames(): array
    {
        return \array_keys(\iterator_to_array($this->iterator));
    }
}
