<?php
namespace Viserio\Http\Uri;

use InvalidArgumentException;
use Viserio\Http\Uri\Traits\{
    HostValidateTrait,
    PortValidateTrait
};

/**
 * A class to parse a URI string according to RFC3986.
 *
 * See the original here: http://bit.ly/1tcJpfG.
 *
 * @author Ignace Nyamagana Butera <nyamsprod@gmail.com>
 */
final class UriParser
{
    use HostValidateTrait;
    use PortValidateTrait;

    const REGEXP_URI = ',^
        ((?<scheme>[^:/?\#]+):)?      # URI scheme component
        (?<authority>//([^/?\#]*))?   # URI authority part
        (?<path>[^?\#]*)              # URI path component
        (?<query>\?([^\#]*))?         # URI query component
        (?<fragment>\#(.*))?          # URI fragment component
    ,x';

    const REGEXP_AUTHORITY = ',^(?<userinfo>(?<ucontent>.*?)@)?(?<hostname>.*?)?$,';

    const REGEXP_REVERSE_HOSTNAME = ',^((?<port>[^(\[\])]*):)?(?<host>.*)?$,';

    const REGEXP_SCHEME = ',^([a-z]([-a-z0-9+.]+)?)?$,i';

    const REGEXP_INVALID_USER = ',[/?#@:],';

    const REGEXP_INVALID_PASS = ',[/?#@],';

   /**
     * Parse a string as an URI according to the regexp form rfc3986
     *
     * @param string $uri
     *
     * @return array
     */
    public function parse(string $uri): array
    {
        $parts = $this->extractUriParts($uri);

        return $this->normalizeUriHash(array_merge(
            $this->parseAuthority($parts['authority']),
            [
                'scheme' => '' === $parts['scheme'] ? null : $parts['scheme'],
                'path' => $parts['path'],
                'query' => '' === $parts['query'] ? null : mb_substr($parts['query'], 1, null, 'UTF-8'),
                'fragment' => '' === $parts['fragment'] ? null : mb_substr($parts['fragment'], 1, null, 'UTF-8'),
            ]
        ));
    }

    /**
     * Normalize URI components hash
     *
     * @param array $components a hash representation of the URI components
     *                          similar to PHP parse_url function result
     *
     * @return array
     */
    protected function normalizeUriHash(array $components)
    {
        return array_replace([
            'scheme' => null,
            'user' => null,
            'pass' => null,
            'host' => null,
            'port' => null,
            'path' => '',
            'query' => null,
            'fragment' => null,
        ], $components);
    }

    /**
     * Extract URI parts
     *
     * @see http://tools.ietf.org/html/rfc3986#appendix-B
     *
     * @param string $uri The URI to split
     *
     * @return string[]
     */
    protected function extractUriParts(string $uri)
    {
        preg_match(self::REGEXP_URI, $uri, $parts);

        $parts += ['query' => '', 'fragment' => ''];

        if (preg_match(self::REGEXP_SCHEME, $parts['scheme'])) {
            return $parts;
        }

        $parts['path'] = $parts['scheme'] . ':' . $parts['authority'] . $parts['path'];
        $parts['scheme'] = '';
        $parts['authority'] = '';

        return $parts;
    }

    /**
     * Parse a URI authority part into its components
     *
     * @param string $authority
     *
     * @return array
     */
    protected function parseAuthority(string $authority): array
    {
        $res = ['user' => null, 'pass' => null, 'host' => null, 'port' => null];

        if ('' === $authority) {
            return $res;
        }

        $content = mb_substr($authority, 2, null, 'UTF-8');

        if ('' === $content) {
            return ['host' => ''] + $res;
        }

        preg_match(self::REGEXP_AUTHORITY, $content, $auth);

        if ('' !== $auth['userinfo']) {
            $userinfo = explode(':', $auth['ucontent'], 2);
            $res = ['user' => array_shift($userinfo), 'pass' => array_shift($userinfo)] + $res;
        }

        return $this->parseHostname($auth['hostname']) + $res;
    }

    /**
     * Parse the hostname into its components Host and Port
     *
     * No validation is done on the port or host component found
     *
     * @param string $hostname
     *
     * @return array
     */
    protected function parseHostname(string $hostname): array
    {
        $components = ['host' => null, 'port' => null];

        $hostname = strrev($hostname);

        if (preg_match(self::REGEXP_REVERSE_HOSTNAME, $hostname, $res)) {
            $components['host'] = strrev($res['host']);
            $components['port'] = strrev($res['port']);
        }

        $components['host'] = $this->validateHost($components['host']);
        $components['port'] = $this->validatePort($components['port']);

        return $components;
    }
}
