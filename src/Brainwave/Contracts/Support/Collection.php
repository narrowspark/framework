<?php

namespace Brainwave\Contracts\Support;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Contracts\Encrypter\Encrypter as EncrypterContract;

/**
 * Collection.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Collection extends \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value);

    /**
     * @param string      $key
     * @param string|null $default
     */
    public function get($key, $default = null);

    /**
     * Add data to set.
     *
     * @param array $items Key-value array of data to append to this set
     *
     * @return \Brainwave\Support\Collection
     */
    public function replace(array $items);

    /**
     * Fetch set data.
     *
     * @return array This set's key-value data array
     */
    public function all();

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     */
    public function remove($key);

    /**
     * Clear all values.
     */
    public function clear();

    /**
     * Encrypt data.
     *
     * @param EncrypterContract $crypt
     */
    public function encrypt(EncrypterContract $crypt);

    /**
     * Decrypt data.
     *
     * @param EncrypterContract $crypt
     */
    public function decrypt(EncrypterContract $crypt);
}
