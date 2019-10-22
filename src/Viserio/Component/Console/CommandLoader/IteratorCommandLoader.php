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

namespace Viserio\Component\Console\CommandLoader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * A simple command loader using generators to instantiate commands lazily.
 */
class IteratorCommandLoader implements CommandLoaderInterface
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
    public function has($name): bool
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
    public function get($name): Command
    {
        if (! $this->has($name)) {
            throw new CommandNotFoundException(\sprintf('Command [%s] does not exist.', $name));
        }

        foreach ($this->iterator as $id => $value) {
            if ($id === $name) {
                return $value;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNames(): array
    {
        return \array_keys(\iterator_to_array($this->iterator));
    }
}
