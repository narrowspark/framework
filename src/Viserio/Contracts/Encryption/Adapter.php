<?php
namespace Viserio\Contracts\Encryption;

/**
 * Adapter.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7
 */
interface Adapter
{
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
    public function encrypt($data);

    /**
     * Strip PKCS7 padding and decrypt
     * data encrypted by encrypt().
     *
     * @param string $data JSON string containing the encrypted data and meta information in the
     *                     excact format as returned by encrypt().
     *
     * @return string Decrypted data in it's original form.
     */
    public function decrypt($data);
}
