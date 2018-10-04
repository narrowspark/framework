<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Compiler;

use Closure;
use Psr\Container\ContainerInterface;
use Roave\BetterReflection\Reflection\ReflectionClass as RoaveReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionFunction as RoaveReflectionFunction;
use Roave\BetterReflection\Reflection\ReflectionMethod as RoaveReflectionMethod;

interface Definition extends DeprecatedDefinition
{
    /**
     * Get the reflector instance.
     *
     * @return null|\ReflectionFunction|RoaveReflectionClass|RoaveReflectionFunction|RoaveReflectionMethod
     */
    public function getReflector();

    /**
     * Replaces a specific argument.
     *
     * @param int|string $index
     * @param mixed      $argument
     *
     * @throws \OutOfBoundsException When the replaced argument does not exist
     *
     * @return void
     */
    public function replaceParameter($index, $argument): void;

    /**
     * Returns the list of arguments to pass when calling the method.
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * Set the name of the compile extend method.
     *
     * @param string $extendCompiledMethodName
     *
     * @return void
     */
    public function setExtendMethodName(string $extendCompiledMethodName): void;

    /**
     * Check if the binding is shared.
     *
     * @return bool
     */
    public function isShared(): bool;

    /**
     * Set the binding lazy.
     *
     * @param bool $bool
     */
    public function setLazy(bool $bool): void;

    /**
     * Check if the binding is lazy.
     *
     * @return bool
     */
    public function isLazy(): bool;

    /**
     * Get the binding name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the binding value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Add a extender to the binding.
     *
     * @param \Closure $extender
     *
     * @return void
     */
    public function addExtender(Closure $extender): void;

    /**
     * Check if the binding has extenders.
     *
     * @return bool
     */
    public function isExtended(): bool;

    /**
     * Check if the binding is resolved.
     *
     * @return bool
     */
    public function isResolved(): bool;

    /**
     * Resolve the bound value.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param array                             $parameters
     *
     * @return void
     */
    public function resolve(ContainerInterface $container, array $parameters = []): void;

    /**
     * Returns the bound value as executable php code for the compiled container.
     *
     * @return string
     */
    public function compile(): string;

    /**
     * Returns debug info for bound value.
     *
     * @return string
     */
    public function getDebugInfo(): string;
}
