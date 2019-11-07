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

namespace Viserio\Component\Support;

use Closure;
use ReflectionException;
use ReflectionFunction;
use Throwable;
use TypeError;
use Viserio\Contract\Support\Stringable;

class LazyString implements Stringable
{
    /** @var string */
    private $value;

    /**
     * {@inheritdoc}
     *
     * @throws ReflectionException
     * @throws Throwable
     */
    public function __toString(): string
    {
        if (\is_string($this->value)) {
            return $this->value;
        }

        try {
            return $this->value = ($this->value)();
        } catch (Throwable $exception) {
            if (TypeError::class === \get_class($exception) && __FILE__ === $exception->getFile()) {
                $type = \explode(', ', $exception->getMessage());
                $type = \substr(\array_pop($type), 0, -\strlen(' returned'));

                $r = new ReflectionFunction($this->value);

                $exception = new TypeError(\sprintf('Return value of %s() passed to %s::fromCallable() must be of the type string, %s returned.', $r->getStaticVariables()['callback'], static::class, $type));
            }

            return $exception->getMessage();
        }
    }

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * @param callable $callback  A callable or a [Closure, method] lazy-callable
     * @param array    $arguments
     *
     * @return static
     */
    public static function fromCallable($callback, ...$arguments): self
    {
        if (! \is_callable($callback) && ! (\is_array($callback) && isset($callback[0]) && $callback[0] instanceof Closure && 2 >= \count($callback))) {
            throw new TypeError(\sprintf('Argument 1 passed to %s() must be a callable or a [Closure, method] lazy-callable, %s given.', __METHOD__, \gettype($callback)));
        }

        $lazyString = new static();
        $lazyString->value = static function () use (&$callback, &$arguments, &$value): string {
            if ($arguments !== null) {
                if (! \is_callable($callback)) {
                    $callback[0] = $callback[0]();
                    $callback[1] = $callback[1] ?? '__invoke';
                }

                $value = $callback(...$arguments);

                $callback = self::getPrettyName($callback);

                $arguments = null;
            }

            return $value ?? '';
        };

        return $lazyString;
    }

    /**
     * @param callable $callback
     *
     * @throws \ReflectionException<
     *
     * @return string
     */
    private static function getPrettyName(callable $callback): string
    {
        if (\is_string($callback)) {
            return $callback;
        }

        if (\is_array($callback)) {
            $class = \is_object($callback[0]) ? \get_class($callback[0]) : $callback[0];
            $method = $callback[1];
        } elseif ($callback instanceof Closure) {
            $r = new ReflectionFunction($callback);

            if (false !== \strpos($r->name, '{closure}') || ! $class = $r->getClosureScopeClass()) {
                return $r->name;
            }

            $class = $class->name;
            $method = $r->name;
        } else {
            $class = \get_class($callback);
            $method = '__invoke';
        }

        if (isset($class[15]) && "\0" === $class[15] && 0 === \strpos($class, "class@anonymous\x00")) {
            $class = \get_parent_class($class) . '@anonymous';
        }

        return $class . '::' . $method;
    }
}
