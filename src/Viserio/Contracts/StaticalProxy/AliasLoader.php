<?php

declare(strict_types=1);
namespace Viserio\Contracts\StaticalProxy;

interface AliasLoader
{
    /**
     * Add an alias to the loader.
     *
     * @param string|string[] $classes
     * @param string|null     $alias
     *
     * @return $this
     */
    public function alias($classes, string $alias = null): AliasLoader;

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
     */
    public function removeAlias();

    /**
     * Resolves a plain alias.
     *
     * @param string $alias
     *
     * @return string|bool
     */
    public function resolveAlias(string $alias);

    /**
     * Registers a class alias.
     *
     * @param string|string[] $patterns
     * @param string|null     $translation
     */
    public function aliasPattern($patterns, string $translation = null);

    /**
     * Removes an alias pattern.
     *
     * @param string      $pattern
     * @param string|null $translation
     */
    public function removeAliasPattern(string $pattern, string $translation = null);

    /**
     * Adds a namespace alias.
     *
     * @param string $class
     * @param string $alias
     */
    public function aliasNamespace(string $class, string $alias);

    /**
     * Resolves a namespace alias.
     *
     * @param string $alias Alias
     *
     * @return string|bool Class name when resolved
     */
    public function resolveNamespaceAlias(string $alias);

    /**
     * Removes a namespace alias.
     */
    public function removeNamespaceAlias();

    /**
     * Register the loader on the auto-loader stack.
     */
    public function register();

    /**
     * Indicates if the loader has been registered.
     *
     * @return bool
     */
    public function isRegistered(): bool;

    /**
     * Unregisters the autoloader function.
     */
    public function unregister();

    /**
     * Set the registered aliases.
     *
     * @param array $aliases
     */
    public function setAliases(array $aliases);

    /**
     * Get the registered aliases.
     *
     * @return array
     */
    public function getAliases(): array;
}
