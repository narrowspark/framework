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

namespace Viserio\Component\Container\Definition\Traits;

use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @property array<string, bool> $changes
 *
 * @internal
 */
trait MethodCallsAwareTrait
{
    /**
     * The called class methods.
     *
     * @var array
     */
    protected $methodCalls = [];

    /**
     * {@inheritdoc}
     */
    public function getMethodCalls(): array
    {
        return $this->methodCalls;
    }

    /**
     * {@inheritdoc}
     */
    public function setMethodCalls(array $calls = [])
    {
        $this->methodCalls = [];
        $this->changes['method_calls'] = false;

        foreach ($calls as $call) {
            $this->addMethodCall($call[0], $call[1] ?? [], $call[2] ?? false);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMethodCall(string $method, array $parameters = [], bool $returnsClone = false)
    {
        if ($method === '') {
            throw new InvalidArgumentException('Method name cannot be empty.');
        }

        $this->changes['method_calls'] = true;
        $this->methodCalls[] = [$method, $parameters, $returnsClone];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeMethodCall(string $method)
    {
        foreach ($this->methodCalls as $i => $call) {
            if ($call[0] === $method) {
                unset($this->methodCalls[$i]);

                break;
            }
        }

        $this->methodCalls = \array_values($this->methodCalls);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMethodCall($method): bool
    {
        foreach ($this->methodCalls as $call) {
            if ($call[0] === $method) {
                return true;
            }
        }

        return false;
    }
}
