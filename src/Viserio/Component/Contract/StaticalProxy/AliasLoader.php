<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\StaticalProxy;

interface AliasLoader
{
    /**
     * Add an alias to the loader.
     *
     * @param string|string[] $classes
     * @param null|string     $alias
     *
     * @return \Viserio\Component\Contract\StaticalProxy\AliasLoader
     */
    public function alias($classes, string $alias = null): self;

    /**
     * Resolves an alias.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function load(string $alias): bool;

    /**
     * Removes an alias.
     *
     * @return void
     */
    public function removeAlias(): void;

    /**
     * Resolves a plain alias.
     *
     * @param string $alias
     *
     * @return bool|string
     */
    public function resolveAlias(string $alias);

    /**
     * Registers a class alias.
     *
     * @param string|string[] $patterns
     * @param null|string     $translation
     *
     * @return void
     */
    public function aliasPattern($patterns, string $translation = null): void;

    /**
     * Removes an alias pattern.
     *
     * @param string      $pattern
     * @param null|string $translation
     *
     * @return void
     */
    public function removeAliasPattern(string $pattern, string $translation = null): void;

    /**
     * Adds a namespace alias.
     *
     * @param string $class
     * @param string $alias
     *
     * @return void
     */
    public function aliasNamespace(string $class, string $alias): void;

    /**
     * Resolves a namespace alias.
     *
     * @param string $alias Alias
     *
     * @return bool|string Class name when resolved
     */
    public function resolveNamespaceAlias(string $alias);

    /**
     * Removes a namespace alias.
     *
     * @return void
     */
    public function removeNamespaceAlias(): void;

    /**
     * Register the loader on the auto-loader stack.
     *
     * @return void
     */
    public function register(): void;

    /**
     * Indicates if the loader has been registered.
     *
     * @return bool
     */
    public function isRegistered(): bool;

    /**
     * Unregisters the autoloader function.
     *
     * @return void
     */
    public function unregister(): void;

    /**
     * Set the registered aliases.
     *
     * @param array $aliases
     *
     * @return void
     */
    public function setAliases(array $aliases): void;

    /**
     * Get the registered aliases.
     *
     * @return array
     */
    public function getAliases(): array;
}
