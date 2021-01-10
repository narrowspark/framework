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

namespace Viserio\Component\Console\CommandLoader;

use IteratorAggregate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * A simple command loader using generators to instantiate commands lazily.
 */
class IteratorCommandLoader implements CommandLoaderInterface
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
