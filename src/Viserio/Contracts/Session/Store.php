<?php
namespace Viserio\Contracts\Session;

interface Store
{
    /**
     * Starts the session storage.
     *
     * @return bool True if session started.
     *
     * @throws \RuntimeException If session fails to start.
     */
    public function start(): bool;

    /**
     * Returns the session ID.
     *
     * @return string The session ID.
     */
    public function getId(): string;

    /**
     * Sets the session ID.
     *
     * @param string $id
     */
    public function setId(string $id);

    /**
     * Returns the session name.
     *
     * @return mixed The session name.
     */
    public function getName();

    /**
     * Sets the session name.
     *
     * @param string $name
     */
    public function setName(string $name);

    /**
     * Invalidates the current session.
     *
     * Clears all session attributes and flashes and regenerates the
     * session and deletes the old session from persistence.
     *
     * @param int $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                      will leave the system settings unchanged, 0 sets the cookie
     *                      to expire with browser session. Time is in seconds, and is
     *                      not a Unix timestamp.
     *
     * @return bool True if session invalidated, false if error.
     */
    public function invalidate(int $lifetime = null): bool;

    /**
     * Migrates the current session to a new session id while maintaining all
     * session attributes.
     *
     * @param bool $destroy  Whether to delete the old session or leave it to garbage collection.
     * @param int  $lifetime Sets the cookie lifetime for the session cookie. A null value
     *                       will leave the system settings unchanged, 0 sets the cookie
     *                       to expire with browser session. Time is in seconds, and is
     *                       not a Unix timestamp.
     *
     * @return bool True if session migrated, false if error.
     */
    public function migrate(bool $destroy = false, int $lifetime = null): bool;

    /**
     * Force the session to be saved and closed.
     *
     * This method is generally not required for real sessions as
     * the session will be automatically saved at the end of
     * code execution.
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
     */
    public function clear();

    /**
     * Checks if the session was started.
     *
     * @return bool
     */
    public function isStarted(): bool;
}
