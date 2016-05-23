<?php
namespace Viserio\Contracts\StaticalProxy;

interface AliasLoader
{
    /**
     * Add an alias to the loader.
     *
     * @param string|string[] $class
     * @param string|null     $alias
     *
     * @return self
     */
    public function alias($class, string $alias = null): self;

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
    public function resolveAlias($alias);

    /**
     * Registers a class alias.
     *
     * @param string|string[] $pattern
     * @param string|null     $translation
     */
    public function aliasPattern($pattern, string $translation = null);

    /**
     * Removes an alias pattern.
     *
     * @param string      $pattern
     * @param string|null $translation
     */
    public function removeAliasPattern($pattern, string $translation = null);

    /**
     * Adds a namespace alias.
     *
     * @param string $class
     * @param string $alias
     */
    public function aliasNamespace($class, string $alias);

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
