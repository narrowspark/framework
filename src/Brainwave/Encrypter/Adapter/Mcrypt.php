<?php

namespace Brainwave\Encrypter\Adapter;

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

use Brainwave\Contracts\Encrypter\Adapter as AdapterContract;
use Brainwave\Contracts\Encrypter\DecryptException;
use Brainwave\Contracts\Hashing\Generator as HashContract;
use RandomLib\Generator as RandomLib;

/**
 * Mcrypt.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
class Mcrypt implements AdapterContract
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
     *
     * @see http://www.php.net/manual/mcrypt.ciphers.php
     */
    protected $cipher = MCRYPT_RIJNDAEL_256;

    /**
     * The mode used for encryption.
     *
     * @var string
     */
    protected $mode = MCRYPT_MODE_CBC;

    /**
     * Padding status.
     *
     * @var Boolean
     */
    protected $padding = false;

    /**
     * A "sliding" Initialization Vector.
     *
     * @var String
     */
    protected $encryptIV;

    /**
     * Mcrypt resource for encryption.
     *
     * The mcrypt resource can be recreated every time something needs to be created or it can be created just once.
     * Since mcrypt operates in continuous mode, by default, it'll need to be recreated when in non-continuous mode.
     */
    protected $enmcrypt;

    /**
     * Mcrypt.
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
     * Setup mcrypt.
     */
    public function setup()
    {
        // Make sure both algorithm and mode are either block or non-block.
        $isBlockCipher = mcrypt_module_is_block_algorithm($this->cipher);
        $isBlockMode = mcrypt_module_is_block_algorithm_mode($this->mode);

        if ($isBlockCipher !== $isBlockMode) {
            throw new DecryptException('You can`t mix block and non-block ciphers and modes');
        }

        $this->enmcrypt = mcrypt_module_open($this->cipher, '', $this->mode, '');

         // Create IV.
        $this->encryptIV = $this->rand->generate(mcrypt_enc_get_iv_size($this->enmcrypt));

        // we need the $ecb mcrypt resource (only) in MODE_CFB with enableContinuousBuffer()
        // to workaround mcrypt's broken ncfb implementation in buffered mode
        if ($this->mode === 'cfb') {
            mcrypt_generic_init($this->enmcrypt, $this->key, str_repeat("\0", $this->encryptIV));
        } else {
            mcrypt_generic_init($this->enmcrypt, $this->key, $this->encryptIV);
        }
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
        // Validate key length
        $this->validateKeyLength($this->key, $this->enmcrypt);

        // Prepeare the array with data.
        $serializedData = serialize($data);

        // Enable padding of data if block cipher moode.
        if (mcrypt_module_is_block_algorithm_mode($this->mode) === true) {
            $this->padding = true;
        }

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
        // Everything looks good so far. Let's continue.
        $demcrypt = mcrypt_module_open($data['algo'], '', $data['mode'], '');

        // Validate key.
        $this->validateKeyLength($this->key, $demcrypt);

        // Init mcrypt.
        mcrypt_generic_init($demcrypt, $this->key, base64_decode($data['iv'], true));

        $decrypted = rtrim(
            mdecrypt_generic(
                $demcrypt,
                base64_decode($this->stripPadding(mcrypt_enc_get_block_size($demcrypt), $data['cdata']), true)
            )
        );

        $this->closeMcrypt($demcrypt);

        return $decrypted;
    }

    /**
     * Close mcrypt.
     *
     * @param resource $demcrypt
     */
    public function closeMcrypt($demcrypt)
    {
        mcrypt_generic_deinit($demcrypt);
        mcrypt_module_close($demcrypt);
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
        $pad = $this->getBlockAndPad($serializedData);

        $padding = $pad['padding'];

        // Algorithm used to encrypt.
        $algo = $this->cipher;
        // Algorithm mode.
        $mode = $this->mode;
        // Initialization vector, just a bunch of randomness.
        $iv = base64_encode($this->encryptIV);
        // The encrypted data.
        $cdata = base64_encode(mcrypt_generic($this->enmcrypt, $pad['serializedData']));
        // The message authentication code. Used to make sure the
        // message is valid when decrypted.
        $mac = base64_encode($this->hash->make($cdata.$this->key, 'pbkdf2'));

        return compact('padding', 'algo', 'mode', 'iv', 'cdata', 'mac');
    }

    /**
     * Add padding if enabled.
     *
     * @param string $serializedData
     *
     * @return array|string
     */
    protected function getBlockAndPad($serializedData)
    {
        $padding = '';

        if ($this->padding === true) {
            $block = mcrypt_enc_get_block_size($this->enmcrypt);
            $serializedData = $this->addPadding($block, $serializedData);
            $padding = 'PKCS7';
        }

        return compact('serializedData', 'padding');
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

    /**
     * Validate encryption key based on valid key sizes for selected cipher and cipher mode.
     *
     * @param string   $key    Encryption key
     * @param resource $module Encryption module
     *
     * @throws DecryptException If key size is invalid for selected cipher
     */
    protected function validateKeyLength($key, $module)
    {
        $keySize = strlen($key);
        $keySizeMin = 32;
        $keySizeMax = mcrypt_enc_get_key_size($module);
        $validKeySizes = mcrypt_enc_get_supported_key_sizes($module);

        if ($validKeySizes) {
            if (!in_array($keySize, $validKeySizes, true)) {
                throw new DecryptException(
                    'Encryption key length must be one of: '.implode(', ', $validKeySizes)
                );
            }
        } elseif ($keySize < $keySizeMin || $keySize > $keySizeMax) {
            throw new DecryptException(sprintf(
                'Encryption key length must be between %s and %s, inclusive',
                $keySizeMin,
                $keySizeMax
            ));
        }
    }
}
