<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Traits;

use BadMethodCallException;
use Closure;

trait MacroableTrait
{
    /**
     * The registered string macros.
     *
     * @var array
     */
    protected static $macros = [];

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf('Method [%s] does not exist.', $method));
        }

        return static::resolveMacroCall($method, $parameters, Closure::bind(static::$macros[$method], null, static::class));
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! static::hasMacro($method)) {
            throw new BadMethodCallException("Method {$method} does not exist.");
        }

        return static::resolveMacroCall($method, $parameters, static::$macros[$method]->bindTo($this, static::class));
    }

    /**
     * Register a custom macro.
     *
     * @param string   $name
     * @param callable $macro
     *
     * @return void
     */
    public static function macro(string $name, callable $macro): void
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Checks if macro is registered.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function hasMacro(string $name): bool
    {
        return isset(static::$macros[$name]);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string   $method
     * @param array    $parameters
     * @param \Closure $bind
     *
     * @return mixed
     */
    protected static function resolveMacroCall(string $method, array $parameters, Closure $bind)
    {
        if (static::$macros[$method] instanceof Closure) {
            return call_user_func_array($bind, $parameters);
        }

        return call_user_func_array(static::$macros[$method], $parameters);
    }
}
