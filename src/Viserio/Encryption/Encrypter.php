<?php
namespace Viserio\Encryption;

use RandomLib\Generator as RandomLib;
use Viserio\Contracts\Encryption\DecryptException;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Contracts\Encryption\EncryptException;
use Viserio\Contracts\Encryption\InvalidKeyException;
use Viserio\Contracts\Hashing\Generator as HashContract;
use Viserio\Encryption\Adapter\OpenSsl;
use Viserio\Support\Arr;

/**
 * Encrypter.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0
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
     * Hash generator instance.
     *
     * @var \Viserio\Contracts\Hashing\Generator
     */
    protected $hash;

    /**
     * RandomLib instance.
     *
     * @var \RandomLib\Generator
     */
    protected $rand;

    /**
     * Extension.
     *
     * @var \Viserio\Contracts\Encryption\Adapter
     */
    protected $generator;

    /**
     * An array of supported ciphers with allowed key lengths.
     *
     * Each element is an array of valid lengths, the first being preferred.
     *
     * @var array
     */
    protected $lengths = [
        'AES-128-CBC' => [16, 32],
        'AES-256-CBC' => [32],
    ];

    /**
     * Constructor.
     *
     * @param \Viserio\Contracts\Hashing\Generator $hash
     * @param \RandomLib\Generator                   $rand
     * @param string                                 $key
     * @param string                                 $cipher
     * @param string                                 $mode
     */
    public function __construct(HashContract $hash, RandomLib $rand, $key, $cipher = 'AES-256', $mode = 'CBC')
    {
        $this->ensureValid($cipher, $mode, $key);

        $this->key  = $key;

        $this->hash = $hash;
        $this->rand = $rand;

        $this->generator = new OpenSsl($this->hash, $this->rand, $this->key, $cipher, $mode);
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
        $value = $this->generator->encrypt($data);

        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }

        return json_encode($value);
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
        // Decode the JSON string
        $data = json_decode($data, true);

        if ($data === null || Arr::check($data, $this->dataStructure, false) !== true) {
            throw new DecryptException('Invalid data passed to decrypt()');
        }

        $decrypted = $this->generator->decrypt($data);

        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }

        // Return decrypted data.
        return unserialize($decrypted);
    }

    /**
     * Get generator.
     *
     * @return \Viserio\Contracts\Encryption\Adapter
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * Throw an exception if the given key is invalid.
     *
     * This ensures that the given key has a valid length for the chosen cipher,
     * while also taking into account backwards compatibility (v5.0 generated
     * 32 byte keys for the AES-128-CBC-cipher).
     *
     * @param string $cipher
     * @param string mode
     * @param string $key
     * @throws \RuntimeException
     * @return void
     *
     */
    public function ensureValid($cipher, $mode, $key)
    {
        $length = mb_strlen($key, '8bit');

        if (isset($this->lengths[$cipher.'-'.$mode]) && in_array($length, $this->lengths[$cipher.'-'.$mode], true)) {
            return;
        }

        $validCiphers = implode(', ', array_keys($this->lengths));

        throw new \RuntimeException("The only supported ciphers are [$validCiphers] with the correct key lengths.");
    }
}
