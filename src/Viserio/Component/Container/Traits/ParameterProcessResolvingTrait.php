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

namespace Viserio\Component\Container\Traits;

use Viserio\Contract\Container\Exception\CircularParameterException;

trait ParameterProcessResolvingTrait
{
    /**
     * An array of keys that are being resolved (used internally to detect circular references).
     *
     * @var array<string, bool>
     */
    protected array $resolvingDynamicParameters = [];

    protected array $cache = [];

    abstract protected function getProcessors(): iterable;

    /**
     * Resolves parameters inside a string.
     *
     * @throws \Viserio\Contract\Container\Exception\CircularParameterException if a circular reference if detected
     * @throws \Viserio\Contract\Container\Exception\RuntimeException           when a given parameter has a type problem
     *
     * @return mixed The resolved string
     */
    private function resolveString(string $expression)
    {
        if (\preg_match('/\{(.+)\|(.*)\}/U', $expression, $matches) === 0) {
            return $expression;
        }

        $value = \array_reduce(\explode('|', $matches[2]), function ($carry, string $method) use ($expression) {
            if ($carry === null) {
                return null;
            }

            $value = "{$carry}|{$method}";

            if (\array_key_exists($value, $this->resolvingDynamicParameters)) {
                throw new CircularParameterException($expression, \array_keys($this->resolvingDynamicParameters));
            }

            if (\array_key_exists($value, $this->cache)) {
                return $this->cache[$value];
            }

            /** @var \Viserio\Contract\Container\Processor\ParameterProcessor $processor */
            foreach ($this->getProcessors() as $processor) {
                if ($processor->supports($value)) {
                    $this->resolvingDynamicParameters[$value] = true;

                    return $processor->process($value);
                }
            }

            return null;
        }, $matches[1]);

        $this->resolvingDynamicParameters = [];

        if (\is_string($value)) {
            return \str_replace($matches[0], $value, $expression);
        }

        return $value;
    }
}
