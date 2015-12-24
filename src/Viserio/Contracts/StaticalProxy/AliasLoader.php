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
     * @return void
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
     * @return void
     */
    public function aliasPattern($pattern, $translation = null);

    /**
     * Removes an alias pattern.
     *
     * @param string      $pattern
     * @param string|null $translation
     * @return void
     */
    public function removeAliasPattern($pattern, $translation = null);

    /**
     * Adds a namespace alias.
     *
     * @param string $class
     * @param string $alias
     * @return void
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
     * @return void
     */
    public function removeNamespaceAlias();

    /**
     * Register the loader on the auto-loader stack.
     * @return void
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
     * @return void
     */
    public function unregister();

    /**
     * Set the registered aliases.
     *
     * @param array $aliases
     * @return void
     */
    public function setAliases(array $aliases);

    /**
     * Get the registered aliases.
     *
     * @return array
     */
    public function getAliases();
}
