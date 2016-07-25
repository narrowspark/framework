<?php

declare(strict_types=1);
namespace Viserio\Http\Uri\Traits;

trait TranscoderTrait
{
    protected static $pathRegexp = "/(?:[^\!\$&'\(\)\*\+,;\_\pL\=\:\/@%]++|%(?![A-Fa-f0-9]{2}))/u";

    protected static $queryFragmentRegexp = "/(?:[^\!\$&'\(\)\*\+,;\=\:\/@%\?]+|%(?![A-Fa-f0-9]{2}))/s";

    protected static $encodedRegexp = ',%(?<encode>[0-9a-fA-F]{2}),';

    protected static $unreservedRegexp = '/[\w\.~]+/';

    /**
     * Reserved characters list
     *
     * @var string
     */
    protected static $reservedCharactersRegex = "\!\$&'\(\)\*\+,;\=:";

    /**
     * Encode a string according to RFC3986 Rules
     *
     * @param string|int $subject
     *
     * @return string
     */
    protected static function encodeQueryFragment($subject): string
    {
        return self::encodeComponent((string) $subject, self::$queryFragmentRegexp);
    }

    /**
     * Encoding string according to RFC3986
     *
     * @param string $subject
     *
     * @return string
     */
    protected static function encode(string $subject): string
    {
        return self::encodeComponent(
            $subject,
            '/(?:[^' . static::$reservedCharactersRegex . ']+|%(?![A-Fa-f0-9]{2}))/S'
        );
    }

    /**
     * Decode a string according to RFC3986 Rules
     *
     * @param string $subject
     *
     * @return string
     */
    protected static function decodeQueryFragment(string $subject): string
    {
        $decoder = function (array $matches) {
            $decode = chr(hexdec($matches['encode']));

            if (preg_match(self::$unreservedRegexp, $decode)) {
                return $matches[0];
            }

            if (preg_match('/[\[\]\+\?:]+/', $decode)) {
                return $decode;
            }

            return rawurldecode($matches[0]);
        };

        return preg_replace_callback(self::$encodedRegexp, $decoder, self::encodeQueryFragment($subject));
    }

    /**
     * Encode a path string according to RFC3986
     *
     * @param string $subject can be a string or an array
     *
     * @return string The same type as the input parameter
     */
    protected static function encodePath(string $subject): string
    {
        return self::encodeComponent($subject, self::$pathRegexp);
    }

    /**
     * Encode a component string
     *
     * @param string $subject The string to encode
     * @param string $regexp  The component specific regular expression
     *
     * @return string
     */
    protected static function encodeComponent(string $subject, string $regexp)
    {
        $encoder = function (array $matches) {
            return rawurlencode($matches[0]);
        };

        $formatter = function (array $matches) {
            return strtoupper($matches[0]);
        };

        $subject = str_replace(
            [
                '%7E', '%21', '%2A', '%27',
                '%28', '%29', '%3B', '%3A',
                '%40', '%26', '%3D', '%2B',
                '%24', '%2C', '%2F', '%3F',
                '%25', '%23', '%5B', '%5D',
            ],
            [
                '~', '!', '*', "'",
                '(', ')', ';', ':',
                '@', '&', '=', '+',
                '$', ',', '/', '?',
                '%', '#', '[', ']',
            ],
            $subject
        );

        $subject = preg_replace_callback($regexp, $encoder, $subject);

        return preg_replace_callback(self::$encodedRegexp, $formatter, $subject);
    }

    /**
     * Decode a path string according to RFC3986
     *
     * @param string $subject can be a string or an array
     *
     * @return string The same type as the input parameter
     */
    protected static function decodePath(string $subject): string
    {
        $decoder = function (array $matches) {
            return rawurldecode($matches[0]);
        };

        return preg_replace_callback(self::$pathRegexp, $decoder, $subject);
    }
}
