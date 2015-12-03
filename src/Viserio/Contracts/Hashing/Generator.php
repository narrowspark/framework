<?php
namespace Viserio\Contracts\Hashing;

/**
 * HashGenerator.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Generator
{
    /**
     * Makes a salted hash from a string.
     *
     * @param string $str    string to hash.
     * @param string $method default method 'bcrypt'.
     *
     * @return string|false
     */
    public function make($str, $method = 'bcrypt');

    /**
     * Check a string against a hash.
     *
     * @param string $str  String to check.
     * @param string $hash The hash to check the string against.
     *
     * @return bool|null Returns true on match.
     */
    public function check($str, $hash);

    /**
     * Returns settings used to generate a hash.
     *
     * @param string $hash Hash to get settings for.
     *
     * @return array Returns an array with settings used to make $hash.
     */
    public function getEncoding($hash);
}
