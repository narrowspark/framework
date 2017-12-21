<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Traits;

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Base32Hex;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\ConstantTime\Hex;
use Viserio\Component\Contract\Encryption\Exception\InvalidTypeException;
use Viserio\Component\Contract\Encryption\Security as SecurityContract;

trait ChooseEncoderTrait
{
    /**
     * Select which encoding/decoding function to use.
     *
     * @param bool|string $chosen
     * @param bool        $decode
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidTypeException
     *
     * @return null|callable (array or string)
     */
    protected static function chooseEncoder($chosen, bool $decode = false): ?callable
    {
        if ($chosen === true) {
            return null;
        }

        $functionName = $decode === true ? 'decode' : 'encode';

        if ($chosen === false || $chosen === SecurityContract::ENCODE_HEX) {
            return \implode(
                '::',
                [
                    Hex::class,
                    $functionName,
                ]
            );
        }

        if ($chosen === SecurityContract::ENCODE_BASE32) {
            return \implode(
                '::',
                [
                    Base32::class,
                    $functionName,
                ]
            );
        }

        if ($chosen === SecurityContract::ENCODE_BASE32HEX) {
            return \implode(
                '::',
                [
                    Base32Hex::class,
                    $functionName,
                ]
            );
        }

        if ($chosen === SecurityContract::ENCODE_BASE64) {
            return \implode(
                '::',
                [
                    Base64::class,
                    $functionName,
                ]
            );
        }

        if ($chosen === SecurityContract::ENCODE_BASE64URLSAFE) {
            return \implode(
                '::',
                [
                    Base64UrlSafe::class,
                    $functionName,
                ]
            );
        }

        throw new InvalidTypeException('Illegal value for encoding choice.');
    }
}
