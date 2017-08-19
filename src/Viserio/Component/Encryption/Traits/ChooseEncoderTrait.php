<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Traits;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base32Hex;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\Hex;
use Viserio\Component\Contracts\Encryption\Exception\InvalidTypeException;
use Viserio\Component\Contracts\Encryption\Security as SecurityContract;

trait ChooseEncoderTrait
{
    /**
     * Select which encoding/decoding function to use.
     *
     * @internal
     *
     * @param string|bool $chosen
     * @param bool        $decode
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidTypeException
     *
     * @return callable (array or string)
     */
    protected static function chooseEncoder($chosen, bool $decode = false)
    {
        if ($chosen === true) {
            return null;
        } elseif ($chosen === false || $chosen === SecurityContract::ENCODE_HEX) {
            return \implode(
                '::',
                [
                    Hex::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        } elseif ($chosen === SecurityContract::ENCODE_BASE32) {
            return \implode(
                '::',
                [
                    Base32::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        } elseif ($chosen === SecurityContract::ENCODE_BASE32HEX) {
            return \implode(
                '::',
                [
                    Base32Hex::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        } elseif ($chosen === SecurityContract::ENCODE_BASE64) {
            return \implode(
                '::',
                [
                    Base64::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        } elseif ($chosen === SecurityContract::ENCODE_BASE64URLSAFE) {
            return \implode(
                '::',
                [
                    Base64UrlSafe::class,
                    $decode ? 'decode' : 'encode',
                ]
            );
        }

        throw new InvalidTypeException('Illegal value for encoding choice.');
    }
}
