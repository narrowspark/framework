<?php
namespace Viserio\Http;

use Psr\Http\Message\UriInterface;
use Pdp\PublicSuffixListManager;
use Pdp\Parser;
use InvalidArgumentException;
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
    const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

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

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withQuery($query)
    {
        $query = $this->filterQueryOrFragment($query);

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
        $fragment = $this->filterQueryOrFragment($fragment);

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
        if ($this->host == '') {
            return '';
        }

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
            $this->getPath(), // Absolute URIs should use a "/" for an empty path
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
        // If there's a single leading forward slash, use parse_url()
        if (preg_match('#^\/{1}[^\/]#', $uri) === 1 || Str::containsAny($uri, [
            '%20', '%21', '%2A', '%27',
            '%28', '%29', '%3B', '%3A',
            '%40', '%26', '%3D', '%2B',
            '%24', '%2C', '%2F', '%3F',
            '%25', '%23', '%5B', '%5D'
        ])) {
            $components = parse_url($uri);
        } else {
            try {
                $components = $this->getPdpParser()->parseUrl($uri)->toArray();
            } catch(InvalidArgumentException $exception) {
                throw new InvalidArgumentException('The source URI string appears to be malformed');
            }
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
        $this->query = isset($components['query']) ? $this->filterQueryOrFragment($components['query']) : '';
        $this->fragment = isset($components['fragment']) ? $this->filterQueryOrFragment($components['fragment']) : '';

        if (isset($components['pass']) != '') {
            $this->userInfo .= ':' . $components['pass'];
        }
    }

    /**
     * @param string $value
     *
     * @return string
     *
     * @throws \InvalidArgumentException If the port is invalid.
     */
    private function filterHost($host): string
    {
        if (! is_string($host)) {
            throw new InvalidArgumentException('Host must be a string');
        }

        return strtolower($host);
    }

    /**
     * @param string $value
     *
     * @return string
     *
     * @throws \InvalidArgumentException If the port is invalid.
     */
    private function filterScheme($scheme): string
    {
        if (! is_string($scheme)) {
            throw new InvalidArgumentException('Scheme must be a string');
        }

        $scheme = str_replace('#:(//)?$#', '', strtolower($scheme));

        return $scheme;
    }

    /**
     * @param int|null $port
     *
     * @return int|null
     *
     * @throws \InvalidArgumentException If the port is invalid.
     */
    private function filterPort($port)
    {
        if ($port === null) {
            return null;
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
     *
     * @throws \InvalidArgumentException If the port is invalid.
     */
    private function filterPath($path): string
    {
        if (! is_string($path)) {
            throw new InvalidArgumentException(
                'Invalid path provided; must be a string'
            );
        }

        $path = preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
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
     * Filter a query string key or value, or a fragment.
     *
     * @param string $value
     *
     * @return string
     *
     * @throws \InvalidArgumentException If Query or fragment is invalid.
     */
    private function filterQueryOrFragment($value): string
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException('Query and fragment must be a string');
        }

        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $value
        );
    }

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @param string $scheme
     * @param int $port
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
    private function createUriString($scheme, $authority, $path, $query, $fragment): string
    {
        $uri = '';

        if ($scheme != '') {
            $uri .= $scheme . ':';
        }

        if ($authority != '') {
            $uri .= '//' . $authority;
        }

        if ($path != '') {
            if ($path[0] !== '/') {
                if ($authority != '') {
                    // If the path is rootless and an authority is present, the path MUST be prefixed by "/"
                    $path = '/' . $path;
                }
            } elseif (isset($path[1]) && $path[1] === '/') {
                if ($authority == '') {
                    // If the path is starting with more than one "/" and no authority is present, the
                    // starting slashes MUST be reduced to one.
                    $path = '/' . ltrim($path, '/');
                }
            }

            $uri .= $path;
        }

        if ($query != '') {
            $uri .= '?' . $query;
        }

        if ($fragment != '') {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    /**
     * [rawurlencodeMatchZero description]
     *
     * @param  array  $match [description]
     *
     * @return string
     */
    private function rawurlencodeMatchZero(array $match): string
    {
        return rawurlencode($match[0]);
    }

    /**
     * @return \Pdp\Parser
     */
    private function getPdpParser() : Parser
    {
        if (!$this->pdpParser instanceof Parser) {
            $this->pdpParser = new Parser((new PublicSuffixListManager())->getList());
        }

        return $this->pdpParser;
    }
}
