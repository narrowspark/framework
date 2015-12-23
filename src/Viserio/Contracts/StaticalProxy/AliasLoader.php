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
    public function alias($class, $alias = null);

    /**
     * Removes an alias.
     *
     * @param string|string[] $class
     */
    public function removeAlias();

    /**
     * Resolves a plain alias.
     *
     * @param string $alias
     *
     * @return string|boolean
     */
    public function resolveAlias($alias);

    /**
     * Registers a class alias.
     *
     * @param string|string[] $pattern
     * @param string|null     $translation
     */
    public function aliasPattern($pattern, $translation = null);

    /**
     * Removes an alias pattern.
     *
     * @param string $pattern
     * @param string $translation
     */
    public function removeAliasPattern($pattern, $translation = null);

    /**
     * Adds a namespace alias.
     *
     * @param string $class
     * @param string $alias
     */
    public function aliasNamespace($class, $alias);

    /**
     * Resolves a namespace alias.
     *
     * @param string $alias Alias
     *
     * @return string|boolean Class name when resolved
     */
    public function resolveNamespaceAlias($alias);

    /**
     * Removes a namespace alias.
     *
     * @param string $class
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
    public function isRegistered();

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
    public function getAliases();
}
