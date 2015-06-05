<?php

namespace Brainwave\Encryption\Adapter;

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

use Brainwave\Contracts\Encryption\Adapter as AdapterContract;
use Brainwave\Contracts\Hashing\Generator as HashContract;
use RandomLib\Generator as RandomLib;

/**
 * OpenSsl.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
class OpenSsl implements AdapterContract
{
    /**
     * RandomLib instance.
     *
     * @var \RandomLib\Generator
     */
    protected $rand;

    /**
     * Hash generator instance.
     *
     * @var \Brainwave\Contracts\Hashing\Generator
     */
    protected $hash;

    /**
     * Encryption key
     * should be correct length for selected cipher.
     *
     * @var string
     */
    protected $key;

    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $cipher = 'AES-256';

    /**
     * The mode used for encryption.
     *
     * @var string
     */
    protected $mode = 'CBC';

    /**
     * A "sliding" Initialization Vector.
     *
     * @var String
     */
    protected $encryptIV;

    /**
     * Openssl.
     *
     * @param HashContract $hash
     * @param RandomLib    $rand
     * @param string       $key
     * @param string       $mode
     * @param string       $cipher
     */
    public function __construct(HashContract $hash, RandomLib $rand, $key, $mode, $cipher)
    {
        $this->hash = $hash;
        $this->rand = $rand;
        $this->key = (string) $key;

        $this->setMode($mode);
        $this->setCipher($cipher);
    }

    /**
     * setup openSsl.
     */
    public function setup()
    {
        return 'OpenSSL';
    }

    /**
     * Encrypt data returning a JSON encoded array safe for storage in a database
     * or file. The array has the following structure before it is encoded:.
     *
     * [
     *   'cdata' => 'Encrypted data, Base 64 encoded',
     *   'iv'    => 'Base64 encoded IV',
     *   'algo'  => 'Algorythm used',
     *   'mode'  => 'Mode used',
     *   'mac'   => 'Message Authentication Code'
     * ]
     *
     * @param mixed $data Data to encrypt.
     *
     * @return array Serialized array containing the encrypted data
     *               along with some meta data.
     */
    public function encrypt($data)
    {
        $ivLength = openssl_cipher_iv_length($this->cipher.'-'.$this->mode);
        $this->encryptIV = openssl_random_pseudo_bytes($ivLength);

        // Prepeare the array with data.
        $serializedData = serialize($data);

        return $this->creatJson($serializedData);
    }

    /**
     * Strip PKCS7 padding and decrypt
     * data encrypted by encrypt().
     *
     * @param string $data JSON string containing the encrypted data and meta information in the
     *                     excact format as returned by encrypt().
     *
     * @return string Decrypted data in it's original form.
     */
    public function decrypt($data)
    {
        // We'll go ahead and remove the PKCS7 padding from the encrypted value before
        // we decrypt it. Once we have the de-padded value, we will grab the vector
        // and decrypt the data, passing back the unserialized from of the value.
        $value = base64_decode($data['cdata'], true);

        $iv = base64_decode($data['iv'], true);

        return rtrim($this->stripPadding(16, $this->doDecrypt($value, $iv)));
    }

    /**
     * Creat json.
     *
     * @param string $serializedData
     *
     * @return array
     */
    protected function creatJson($serializedData)
    {
        $serializedData = $this->addPadding(16, $serializedData);
        $padding = 'PKCS7';

        // Algorithm used to encrypt.
        $algo = $this->cipher;
        // Algorithm mode.
        $mode = $this->mode;
        // Initialization vector, just a bunch of randomness.
        $iv = base64_encode($this->encryptIV);
        // The encrypted data.
        $cdata = base64_encode($this->doEncrypt($serializedData, $this->encryptIV));
        // The message authentication code. Used to make sure the
        // message is valid when decrypted.
        $mac = base64_encode($this->hash->make($cdata.$this->key, 'pbkdf2'));

        return compact('padding', 'algo', 'mode', 'iv', 'cdata', 'mac');
    }

    /**
     * Actually encrypt the value using the given Iv with the openssl library encrypt function.
     *
     * @param string $value
     * @param string $iv
     *
     * @return string
     */
    protected function doEncrypt($value, $iv)
    {
        return openssl_encrypt($value, $this->cipher.'-'.$this->mode, $this->key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Actually decrypt the value using the given Iv with the openssl library decrypt function.
     *
     * @param string $value
     * @param string $iv
     *
     * @return string
     */
    protected function doDecrypt($value, $iv)
    {
        return openssl_decrypt($value, $this->cipher.'-'.$this->mode, $this->key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Set crypt mode.
     *
     * @param string|null $mode
     */
    protected function setMode($mode = null)
    {
        if (null === $mode) {
            return;
        }

        $this->mode = $mode;
    }

    /**
     * Set the encryption cipher.
     *
     * @param string|null $cipher
     */
    protected function setCipher($cipher = null)
    {
        if (null === $cipher) {
            return;
        }

        $this->cipher = $cipher;
    }

    /**
     * PKCS7-pad data.
     * Add bytes of data to fill up the last block.
     * PKCS7 padding adds bytes with the same value that the number of bytes that are added.
     *
     * @see http://tools.ietf.org/html/rfc5652#section-6.3
     *
     * @param int    $block Block size.
     * @param string $data  Data to pad.
     *
     * @return string Padded data.
     */
    protected function addPadding($block, $data)
    {
        $pad = $block - (strlen($data) % $block);
        $data .= str_repeat(chr($pad), $pad);

        return $data;
    }

    /**
     * Strip PKCS7-padding.
     *
     * @param int    $block Block size.
     * @param string $data  Padded data.
     *
     * @return string Original data.
     */
    protected function stripPadding($block, $data)
    {
        $pad = ord($data[(strlen($data)) - 1]);

        // Check that what we have at the end of the string really is padding, and if it is remove it.
        if ($pad && $pad < $block && preg_match('/'.chr($pad).'{'.$pad.'}$/', $data)) {
            return substr($data, 0, -$pad);
        }

        return $data;
    }
}
