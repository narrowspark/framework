<?php
namespace Viserio\Hashing;

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

use Viserio\Contracts\Hashing\Password as PasswordContract;

/**
 * Password.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Password implements PasswordContract
{
    /**
     * Default crypt cost factor.
     *
     * @var int
     */
    protected $rounds = 10;

    /**
     * Hash the given value.
     *
     * @param string $value
     * @param array  $options
     *
     * @throws \RuntimeException
     *
     * @return null|string
     */
    public function make($value, array $options = [])
    {
        $cost = isset($options['rounds']) ? $options['rounds'] : $this->rounds;
        $hash = password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);

        if ($hash === false) {
            throw new \RuntimeException('Bcrypt hashing not supported.');
        }

        return $hash;
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param string $value
     * @param string $hashedValue
     * @param array  $options
     *
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        return password_verify($value, $hashedValue);
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param string $hashedValue
     * @param array  $options
     *
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        $cost = isset($options['rounds']) ? $options['rounds'] : $this->rounds;

        return password_needs_rehash($hashedValue, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}
