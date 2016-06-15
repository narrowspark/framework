<?php
namespace Viserio\Contracts\Session;

interface Store
{
    const METADATA_NAMESPACE = '__metadata__';

    /**
     * Starts the session storage.
     * It should be called only once at the beginning. If called for existing
     * session it ovewrites it (clears all values etc).
     *
     * @return bool True if session started.
     */
    public function start(): bool;

    /**
     * Opens the session (for a given request).
     *
     * If called earlier, then second (and next ones) call does nothing.
     *
     * @return bool True if session started.
     */
    public function open(): bool;

    /**
     * Sets the session ID.
     *
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id);

    /**
     * Returns the session ID.
     *
     * @return string The session ID.
     */
    public function getId(): string;

    /**
     * Sets the session name.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name);

    /**
     * Returns the session name.
     *
     * @return string The session name.
     */
    public function getName();

    /**
     * Invalidates the current session.
     *
     * Clears all session attributes and flashes and regenerates the
     * session and deletes the old session from persistence.
     *
     * @return bool True if session invalidated, false if error.
     */
    public function invalidate(): bool;

    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     *
     * @param bool $destroy  Whether to delete the old session or leave it to garbage collection.
     *
     * @return bool True if session migrated, false if error.
     */
    public function migrate(bool $destroy = false): bool;

    /**
     * Force the session to be saved and closed.
     *
     * This method is generally not required for real sessions as
     * the session will be automatically saved at the end of
     * code execution.
     *
     * @return void
     */
    public function save();

    /**
     * Checks if an attribute is defined.
     *
     * @param string $name The attribute name
     *
     * @return bool true if the attribute is defined, false otherwise
     */
    public function has(string $name): bool;

    /**
     * Returns an attribute.
     *
     * @param string $name    The attribute name
     * @param mixed  $default The default value if not found.
     *
     * @return mixed
     */
    public function get(string $name, $default = null);

    /**
     * Sets an attribute.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $name, $value);

    /**
     * Returns attributes.
     *
     * @return array Attributes
     */
    public function all(): array;

    /**
     * Sets attributes.
     *
     * @param array $attributes Attributes
     *
     * @return void
     */
    public function replace(array $attributes);

    /**
     * Removes an attribute.
     *
     * @param string $name
     *
     * @return mixed The removed value or null when it does not exist
     */
    public function remove(string $name);

    /**
     * Clears all attributes.
     *
     * @return void
     */
    public function clear();

    /**
     * Checks if the session was started.
     *
     * @return bool
     */
    public function isStarted(): bool;
}
