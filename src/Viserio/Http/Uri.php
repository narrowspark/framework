<?php
namespace Viserio\Http;

use InvalidArgumentException;
use Pdp\Parser;
use Pdp\PublicSuffixListManager;
use Psr\Http\Message\UriInterface;
use Viserio\Support\Str;

class Uri implements UriInterface
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
     * Absolute http and https URIs require a host per RFC 7230 Section 2.7
     * but in generic URIs the host can be empty. So for http(s) URIs
     * we apply this default host when no host is given yet to form a
     * valid URI.
     */
    const HTTP_DEFAULT_HOST = 'localhost';

    /**
     * Supported Schemes.
     *
     * @var array
     */
    protected $allowedSchemes = [
        'http' => 80,
        'https' => 443,
    ];

    /**
     * Pdp Parser instance.
     *
     * @var \Pdp\Parser
     */
    protected $pdpParser;

    /**
     * Uri scheme (without "://" suffix).
     *
     * @var string
     */
    protected $scheme = '';

    /**
     * Uri user.
     *
     * @var string
     */
    protected $user = '';

    /**
     * Uri password.
     *
     * @var string
     */
    protected $password = '';

    /**
     * Uri host.
     *
     * @var string
     */
    protected $host = '';

    /**
     * Uri port number.
     *
     * @var null|int
     */
    protected $port;

    /**
     * Uri path.
     *
     * @var string
     */
    protected $path = '';

    /**
     * User infos.
     *
     * @var string
     */
    protected $userInfo = '';

    /**
     * Uri query string (without "?" prefix).
     *
     * @var string
     */
    protected $query = '';

    /**
     * Uri fragment string (without "#" prefix).
     *
     * @var string
     */
    protected $fragment = '';

    /**
     * generated uri string cache
     *
     * @var string|null
     */
    private $uriString;

    /**
     * @param string $uri
     */
    public function __construct(string $uri = '')
    {
        if ($uri != '') {
            $this->parseUri($uri);
        }
    }

    /**
     * Operations to perform on clone.
     *
     * Since cloning usually is for purposes of mutation, we reset the
     * $uriString property so it will be re-calculated.
     */
    public function __clone()
    {
        $this->uriString = null;
    }

    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);

        if ($this->scheme === $scheme) {
            return $this;
        }

        $new = clone $this;
        $new->scheme = $scheme;
        $new->port = $new->filterPort($new->port);
        $new->validateState();

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null)
    {
        $info = $user;

        if ($password != '') {
            $info .= ':' . $password;
        }

        if ($this->userInfo === $info) {
            return $this;
        }

        $new = clone $this;
        $new->userInfo = $info;
        $new->validateState();

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withHost($host)
    {
        $host = $this->filterHost($host);

        if ($this->host === $host) {
            return $this;
        }

        $new = clone $this;
        $new->host = $host;
        $new->validateState();

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPort($port)
    {
        $port = $this->filterPort($port);

        if ($this->port === $port) {
            return $this;
        }

        $new = clone $this;
        $new->port = $port;
        $new->validateState();

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withPath($path)
    {
        $path = $this->filterPath($path);

        if ($this->path === $path) {
            return $this;
        }

        $new = clone $this;
        $new->path = $path;
        $new->validateState();

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        $query = $this->filterQuery($query);

        if ($this->query === $query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $query;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment)
    {
        $fragment = $this->filterFragment($fragment);

        if ($this->fragment === $fragment) {
            return $this;
        }

        $new = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority()
    {
        $authority = $this->host;

        if ($this->userInfo != '') {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if ($this->uriString !== null) {
            return $this->uriString;
        }

        $this->uriString = static::createUriString(
            $this->scheme,
            $this->getAuthority(),
            $this->path, // Absolute URIs should use a "/" for an empty path
            $this->query,
            $this->fragment
        );

        return $this->uriString;
    }

    /**
     * Parse a URI into its parts, and set the properties
     *
     * @param string $uri
     */
    private function parseUri($uri)
    {
        if (!$this->pdpParser instanceof Parser) {
            $this->pdpParser = new Parser((new PublicSuffixListManager())->getList());
        }

        try {
            $components = $this->pdpParser->parseUrl($uri)->toArray();
        } catch (InvalidArgumentException $exception) {
            $components = parse_url($uri);
        }

        if ($components === false) {
            throw new InvalidArgumentException('The source URI string appears to be malformed');
        }

        $this->createFromComponents($components);
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components a hash representation of the URI similar to PHP parse_url function result
     */
    private function createFromComponents(array $components)
    {
        $this->scheme = isset($components['scheme']) ? $this->filterScheme($components['scheme']) : '';
        $this->userInfo = $components['user'] ?? '';
        $this->host = isset($components['host']) ? $this->filterHost($components['host']) : '';
        $this->port = isset($components['port']) ? $this->filterPort($components['port']) : null;
        $this->path = isset($components['path']) ? $this->filterPath($components['path']) : '';
        $this->query = isset($components['query']) ? $this->filterQuery($components['query']) : '';
        $this->fragment = isset($components['fragment']) ? $this->filterFragment($components['fragment']) : '';

        if (isset($components['pass']) != '') {
            $this->userInfo .= ':' . $components['pass'];
        }
    }

    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    private function filterHost(string $host): string
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
    private function isValidHost(string $host): bool
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
    private function filterScheme(string $scheme): string
    {
        $scheme = str_replace('#:(//)?$#', '', strtolower($scheme));

        return $scheme;
    }

    /**
     * @param int|null $port
     *
     * @throws InvalidArgumentException
     *
     * @return int|null
     */
    private function filterPort($port)
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
    private function filterPath(string $path): string
    {
        $path = preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'rawurlencodeMatchZero'],
            $path
        );

        if ($path != '') {
            // No path
            return $path;
        }

        if ($path[0] !== '/') {
            // Relative path
            return $path;
        }

        // Ensure only one leading slash, to prevent XSS attempts.
        return '/' . ltrim($path, '/');
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
    private function filterQuery(string $query): string
    {
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
    private function splitQueryValue(string $value): array
    {
        $data = explode('=', $value, 2);

        if (1 === count($data)) {
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
    private function filterFragment(string $fragment): string
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
    private function filterQueryOrFragment(string $str): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $str
        );
    }

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @param string $scheme
     * @param int    $port
     *
     * @return bool
     */
    private function isNonStandardPort($scheme, $port): bool
    {
        return ! isset($this->allowedSchemes[$scheme]) || $this->allowedSchemes[$scheme] !== $port;
    }

    /**
     * Create a URI string from its various parts
     *
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param string $query
     * @param string $fragment
     *
     * @return string
     */
    private function createUriString(
        string $scheme,
        string $authority,
        string $path,
        string $query,
        string $fragment
    ): string {
        $uri = '';

        if ($scheme != '') {
            $uri .= $scheme . ':';
        }

        if ($authority != '') {
            $uri .= '//' . $authority;
        }

        $uri .= $path;

        if ($query != '') {
            $uri .= '?' . $query;
        }

        if ($fragment != '') {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    /**
     * @param array $match
     *
     * @return string
     */
    private function rawurlencodeMatchZero(array $match): string
    {
        return rawurlencode($match[0]);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateState()
    {
        if ($this->host === '' && ($this->scheme === 'http' || $this->scheme === 'https')) {
            $this->host = self::HTTP_DEFAULT_HOST;
        }

        if ($this->getAuthority() === '') {
            if (strpos($this->path, '//') === 0) {
                throw new InvalidArgumentException(
                    'The path of a URI without an authority must not start with two slashes "//"'
                );
            }
        } elseif (isset($this->path[0]) && $this->path[0] !== '/') {
            throw new InvalidArgumentException(
                'The path of a URI with an authority must start with a slash "/" or be empty'
            );
        }

        if ($this->scheme === '' && strpos(explode('/', $this->path, 2)[0], ':') !== false) {
            throw new InvalidArgumentException(
                'A relative URI must not have a path beginning with a segment containing a colon'
            );
        }
    }
}
