<?php
namespace Viserio\Http;

use InvalidArgumentException;

class UriHelper
{
    /**
     * Sub-delimiters used in query strings and fragments.
     *
     * @const string
     */
    const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    /**
     * Unreserved characters used in paths, query strings, and fragments.
     *
     * @const string
     */
    const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~\pL';

    /**
     * Does a UTF-8 safe version of PHP parse_url function.
     *
     * @param string $uri
     *
     * @return array
     */
    protected function parseUri($url): array
    {
        // Create encoded URL with special URL characters decoded so it can be parsed
        // All other characters will be encoded
        $encodedURL = rawurlencode($url);

        $encodedParts = parse_url($encodedURL);

        if ($encodedParts === false) {
            throw new InvalidArgumentException('The source URI string appears to be malformed');
        }

        // Now, decode each value of the resulting array
        $components = [];

        foreach ($encodedParts as $key => $value) {
            $components[$key] = rawurlencode($value);
        }

        return $components;
    }

    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function filterHost(string $host): string
    {
        if ($this->isValidHost($host)) {
            return strtolower($host);
        }

        throw new InvalidArgumentException(
            sprintf('Invalid host: %d.', $host)
        );
    }

    /**
     * Check if host is valid.
     *
     * @param string $host
     *
     * @return bool
     */
    protected function isValidHost(string $host): bool
    {
        if (preg_match('#[\[\]]#', $host)) {
            if (preg_match('#^\[(.+)\]$#', $host, $matches)) {
                if (filter_var($matches[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    return true;
                }

                if (preg_match(
                    '#^v[\da-fA-F]+\.[' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . ']+$#',
                    $matches[1]
                )) {
                    return true;
                }
            }

            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }

        return preg_match('#^([' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . ']|(%[\da-fA-F]{2}))*$#', $host);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function filterScheme(string $scheme): string
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);

        if (empty($scheme)) {
            return '';
        }

        return $scheme;
    }

    /**
     * @param int|null $port
     *
     * @throws InvalidArgumentException
     *
     * @return int|null
     */
    protected function filterPort($port)
    {
        if ($port === null) {
            return;
        }

        $port = (int) $port;

        if (1 > $port || 0xffff < $port) {
            throw new InvalidArgumentException(
                sprintf('Invalid port: %d. Must be between 1 and 65535', $port)
            );
        }

        return $this->isNonStandardPort($this->scheme, $port) ? $port : null;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function filterPath(string $path): string
    {
        if (strpos($path, '?') !== false || strpos($path, '#') !== false) {
            throw new InvalidArgumentException('Invalid path provided; Path should not contain `?` and `#` symbols.');
        }

        $path = $this->cleanPath($path);

        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'rawurlencodeMatchZero'],
            $path
        );
    }

    /**
     * parseQuery
     *
     * @param string $query
     *
     * @return arry
     */
    protected function parseQuery(string $query): array
    {
        parse_str($query, $vars);

        return $vars;
    }

    /**
     * Filter a query string to ensure it is propertly encoded.
     *
     * Ensures that the values in the query string are properly urlencoded.
     *
     * @param string $query
     *
     * @return string
     */
    protected function filterQuery(string $query): string
    {
        if (! empty($query) && strpos($query, '?') === 0) {
            $query = substr($query, 1);
        }

        $parts = explode('&', $query);

        foreach ($parts as $index => $part) {
            list($key, $value) = $this->splitQueryValue($part);

            if ($value === null) {
                $parts[$index] = $this->filterQueryOrFragment($key);
                continue;
            }

            $parts[$index] = sprintf(
                '%s=%s',
                $this->filterQueryOrFragment($key),
                $this->filterQueryOrFragment($value)
            );
        }

        return implode('&', $parts);
    }

    /**
     * Split a query value into a key/value tuple.
     *
     * @param string $value
     *
     * @return array A value with exactly two elements, key and value
     */
    protected function splitQueryValue(string $value): array
    {
        $data = explode('=', $value, 2);

        if (count($data) === 1) {
            $data[] = null;
        }

        return $data;
    }

    /**
     * Filter a fragment value to ensure it is properly encoded.
     *
     * @param string $fragment
     *
     * @return string
     */
    protected function filterFragment(string $fragment): string
    {
        if ($fragment != '' && strpos($fragment, '#') === 0) {
            $fragment = '%23' . substr($fragment, 1);
        }

        return $this->filterQueryOrFragment($fragment);
    }

    /**
     * Filter a query string key or value, or a fragment.
     *
     * @param string $str
     *
     * @return string
     */
    protected function filterQueryOrFragment(string $str): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $str
        );
    }

    /**
     * @param array $match
     *
     * @return string
     */
    protected function rawurlencodeMatchZero(array $match): string
    {
        return rawurlencode($match[0]);
    }

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @param string $scheme
     * @param int    $port
     *
     * @return bool
     */
    protected function isNonStandardPort($scheme, $port): bool
    {
        return ! isset($this->allowedSchemes[$scheme]) || $this->allowedSchemes[$scheme] !== $port;
    }

    /**
     * Resolves //, ../ and ./ from a path and returns
     * the result. Eg:
     *
     * /foo/bar/../boo.php  => /foo/boo.php
     * /foo/bar/../../boo.php => /boo.php
     * /foo/bar/.././/boo.php => /foo/boo.php
     *
     * @param string $path The URI path to clean.
     *
     * @return string Cleaned and resolved URI path.
     */
    private function cleanPath(string $path): string
    {
        $path = preg_replace('#(/+)#', '/', $path);
        $path = explode('/', $path);

        for ($i = 0, $n = count($path); $i < $n; ++$i) {
            if ($path[$i] == '.' || $path[$i] == '..') {
                if (($path[$i] == '.') || ($path[$i] == '..' && $i == 1 && $path[0] == '')) {
                    unset($path[$i]);
                    $path = array_values($path);
                    --$i;
                    --$n;
                } elseif ($path[$i] == '..' && ($i > 1 || ($i == 1 && $path[0] != ''))) {
                    unset($path[$i], $path[$i - 1]);

                    $path = array_values($path);
                    $i -= 2;
                    $n -= 2;
                }
            }
        }

        return implode('/', $path);
    }
}
