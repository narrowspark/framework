<?php
namespace Viserio\Contracts\Session;

interface Factory
{
    /**
     * Returns the value of a key in the segment.
     *
     * @param string $key The key in the segment.
     * @param mixed  $alt An alternative value to return if the key is not set.
     *
     * @return mixed
     */
    public function get(string $key, $alt = null);

    /**
     * Sets the value of a key in the segment.
     *
     * @param string $key The key to set.
     * @param mixed  $val The value to set it to.
     */
    public function set($key, $val);

    /**
     * Clear all data from the segment.
     */
    public function clear();

    /**
     * Sets a flash value for the *next* request.
     *
     * @param string $key The key for the flash value.
     * @param mixed  $val The flash value itself.
     */
    public function setFlash(string $key, $val);

    /**
     * Gets the flash value for a key in the *current* request.
     *
     * @param string $key The key for the flash value.
     * @param mixed  $alt An alternative value to return if the key is not set.
     *
     * @return mixed The flash value itself.
     */
    public function getFlash(string $key, $alt = null);

    /**
     * Clears flash values for *only* the next request.
     */
    public function clearFlash();

    /**
     * Gets the flash value for a key in the *next* request.
     *
     * @param string $key The key for the flash value.
     * @param mixed  $alt An alternative value to return if the key is not set.
     *
     * @return mixed The flash value itself.
     */
    public function getFlashNext($key, $alt = null);

    /**
     * Sets a flash value for the *next* request *and* the current one.
     *
     * @param string $key The key for the flash value.
     * @param mixed  $val The flash value itself.
     */
    public function setFlashNow($key, $val);

    /**
     * Clears flash values for *both* the next request *and* the current one.
     */
    public function clearFlashNow();

    /**
     * Retains all the current flash values for the next request; values that
     * already exist for the next request take precedence.
     */
    public function keepFlash();
}
