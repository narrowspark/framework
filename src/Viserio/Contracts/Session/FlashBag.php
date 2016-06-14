<?php
namespace Viserio\Contracts\Session;

interface FlashBag
{
    /**
     * Get flash name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set flash name.
     *
     * @param string $name [description]
     */
    public function setName(string $name);

    /**
     * Adds a flash message for type.
     *
     * @param string $type
     * @param string $message
     */
    public function add(string $type, string $message);

    /**
     * Registers a message for a given type.
     *
     * @param string|array $type
     * @param string|array $message
     */
    public function set(string $type, $message);

    /**
     * Gets flash messages for a given type.
     *
     * @param string $type    Message category type.
     * @param array  $default Default value if $type does not exist.
     *
     * @return array
     */
    public function peek(string $type, array $default = []): array;

    /**
     * Gets all flash messages.
     *
     * @return array
     */
    public function peekAll(): array;

    /**
     * Gets and clears flash from the stack.
     *
     * @param string $type
     * @param array  $default Default value if $type does not exist.
     *
     * @return array
     */
    public function get(string $type, array $default = []): array;

    /**
     * Gets and clears display flashes from the stack.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Sets all flash messages.
     */
    public function setAll(array $messages);

    /**
     * Has flash messages for a given type?
     *
     * @param string $type
     *
     * @return bool
     */
    public function has(string $type): bool;

    /**
     * Returns a list of all defined types.
     *
     * @return array
     */
    public function keys(): array;

    /**
     * The storage key for flashes in the session.
     *
     * @return string
     */
    public function getStorageKey(): string;

    /**
     * Gets and clears flashes from the stack.
     *
     * @return array
     */
    public function clear(): array;
}
