<?php
namespace Viserio\Http\Uri;

class UriParser
{
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
     * Does a UTF-8 safe version of PHP parse_url function.
     *
     * @param string $uri
     *
     * @return array
     */
    public function parseUri($url): array
    {
        // Build arrays of values we need to decode before parsing
        $entities = ['%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%24', '%2C', '%2F', '%3F', '%23', '%5B', '%5D'];
        $replacements = ['!', '*', "'", '(', ')', ';', ':', '@', '&', '=', '$', ',', '/', '?', '#', '[', ']'];

        // Create encoded URL with special URL characters decoded so it can be parsed
        // All other characters will be encoded
        $encodedURL = str_replace($entities, $replacements, urlencode($url));

        $encodedParts = parse_url($encodedURL);

        if ($encodedParts === false) {
            throw new InvalidArgumentException('The source URI string appears to be malformed');
        }

        // Now, decode each value of the resulting array
        $components = [];

        foreach ($encodedParts as $key => $value) {
            $components[$key] = urldecode(str_replace($replacements, $entities, $value));
        }

        return $components;
    }
}
