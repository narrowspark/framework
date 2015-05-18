<?php

namespace Brainwave\Encrypter;

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
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Encrypter\DecryptException;
use Brainwave\Contracts\Encrypter\Encrypter as EncrypterContract;
use Brainwave\Contracts\Encrypter\InvalidKeyException;
use Brainwave\Contracts\Hashing\Generator as HashContract;
use Brainwave\Encrypter\Adapter\Mcrypt;
use Brainwave\Encrypter\Adapter\OpenSsl;
use Brainwave\Support\Arr;
use RandomLib\Generator as RandomLib;

/**
 * Encrypter.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class Encrypter implements EncrypterContract
{
    /**
     * Encryption key
     * should be correct length for selected cipher.
     *
     * @var string
     */
    protected $key;

    /**
     * Supported data structure.
     *
     * @var array
     */
    protected $dataStructure = [
        'algo' => true,
        'mode' => true,
        'iv' => true,
        'cdata' => true,
        'mac' => true,
    ];

    /**
     * Holds which crypt engine internaly should be use,
     * which will be determined automatically.
     *
     * @var string
     */
    protected $engine = '';

    /**
     * Hash generator instance.
     *
     * @var \Brainwave\Contracts\Hashing\Generator
     */
    protected $hash;

    /**
     * RandomLib instance.
     *
     * @var \RandomLib\Generator
     */
    protected $rand;

    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $cipher = null;

    /**
     * The mode used for encryption.
     *
     * @var string
     */
    protected $mode = null;

    /**
     * Extension.
     *
     * @var \Brainwave\Contracts\Encrypter\Adapter
     */
    protected $generator;

    /**
     * Constructor.
     *
     * @param \Brainwave\Contracts\Hashing\Generator $hash
     * @param \RandomLib\Generator                   $rand
     * @param string                                 $key  Encryption key
     */
    public function __construct(HashContract $hash, RandomLib $rand, $key)
    {
        $this->key = (string) $key;
        $this->hash = $hash;
        $this->rand = $rand;

        if (!extension_loaded('mcrypt') && !extension_loaded('openssl')) {
            throw new DecryptException('Narrowspark requires the Mcrypt or Openssl PHP extension.'.PHP_EOL);
        }

        $this->checkExtension();
    }

    /**
     * Check witch extension to use.
     *
     * Openssl is 30x faster as mcrypt
     *
     * @return bool|null
     */
    protected function checkExtension()
    {
        if (extension_loaded('openssl')) {
            $this->engine = 'openssl';
            $this->generator = new OpenSsl($this->hash, $this->rand, $this->key, $this->mode, $this->cipher);
        } elseif (extension_loaded('mcrypt')) {
            $this->engine = 'mcrypt';
            $this->generator = new Mcrypt($this->hash, $this->rand, $this->key, $this->mode, $this->cipher);
        }

        $this->generator->setup();
    }

    /**
     * Set the encryption key.
     *
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = (string) $key;

        return $this;
    }

    /**
     * Set crypt mode.
     *
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * Set the encryption cipher.
     *
     * @param string $cipher
     */
    public function setCipher($cipher)
    {
        $this->cipher = $cipher;
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
     * @return string Serialized array containing the encrypted data
     *                along with some meta data.
     */
    public function encrypt($data)
    {
        $this->checkKey();

        return json_encode($this->generator->encrypt($data));
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
        $this->checkKey();

        // Decode the JSON string
        $data = json_decode($data, true);

        if ($data === null || Arr::check($data, $this->dataStructure, false) !== true) {
            throw new DecryptException('Invalid data passed to decrypt()');
        }

        // Return decrypted data.
        return unserialize($this->generator->decrypt($data));
    }

    /**
     * Get generator.
     *
     * @return \Brainwave\Contracts\Encrypter\Adapter
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * Check the current key is usable to perform cryptographic operations.
     *
     * @throws \Brainwave\Encryption\InvalidKeyException
     */
    protected function checkKey()
    {
        if ($this->key === '' || $this->key === 'SomeRandomString') {
            throw new InvalidKeyException('The encryption key must be not be empty.');
        }

        if (strlen($this->key) < '32') {
            throw new InvalidKeyException('The encryption key must be a random string.');
        }
    }
}
