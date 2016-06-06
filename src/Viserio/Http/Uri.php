<?php
namespace Viserio\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Viserio\Http\Uri\UriParser;
use Viserio\Http\Uri\Traits\UriBuilderTrait;

class Uri implements UriInterface
{
    use UriBuilderTrait;

    /**
     * Absolute http and https URIs require a host per RFC 7230 Section 2.7
     * but in generic URIs the host can be empty. So for http(s) URIs
     * we apply this default host when no host is given yet to form a
     * valid URI.
     */
    const HTTP_DEFAULT_HOST = 'localhost';

    /**
     * Sub-delimiters used in query strings and fragments.
     *
     * @const string
     */
    protected static $CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

    /**
     * Unreserved characters used in paths, query strings, and fragments.
     *
     * @const string
     */
    protected static $CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~\pL';

    /**
     * Supported Schemes.
     *
     * @var array
     */
    protected $allowedSchemes = [
        'http' => 80,
        'https' => 443,
        'ftp'   => 21,
        'sftp'  => 22,
    ];

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
     * Query variable hash.
     *
     * @var array
     */
    protected $queryVars = [];

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
            $this->createFromComponents(
                (new UriParser)->parse($uri)
            );
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
    public function __toString()
    {
        if ($this->uriString !== null) {
            return $this->uriString;
        }

        $this->uriString = $this->createUriString(
            $this->scheme,
            $this->getAuthority(),
            $this->path,
            $this->getQuery(),
            $this->fragment
        );

        return $this->uriString;
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
        $new->port = $new->validatePort($new->port);
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
        $host = strtolower($host);
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
        $port = $this->validatePort($port);

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
        $new->queryVars = $this->parseQuery($query);
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
        // If the query is empty build it first
        if (is_null($this->query)) {
            $this->query = $this->buildQuery($this->queryVars);
        }

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
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components a hash representation of the URI similar to PHP parse_url function result
     */
    private function createFromComponents(array $components)
    {
        // We need to replace &amp; with & for parse_str to work right...
        if (isset($components['query']) && strpos($components['query'], '&amp;')) {
            $components['query'] = str_replace('&amp;', '&', $components['query']);
        }

        $this->scheme = isset($components['scheme']) ? $this->filterScheme($components['scheme']) : '';
        $this->userInfo = $components['user'] ?? '';
        $this->host = isset($components['host']) ? strtolower($components['host']) : '';
        $this->port = $components['port'] ?? null;
        $this->path = isset($components['path']) ? $this->filterPath($components['path']) : '';
        $this->query = isset($components['query']) ? $this->filterQuery($components['query']) : '';
        $this->fragment = isset($components['fragment']) ? $this->filterFragment($components['fragment']) : '';

        if (isset($components['pass']) != '') {
            $this->userInfo .= ':' . $components['pass'];
        }

        // Parse the query
        if (isset($components['query'])) {
            $this->queryVars = $this->parseQuery($components['query']);
        }
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
            '/(?:[^' . self::$CHAR_UNRESERVED . self::$CHAR_SUB_DELIMS . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/u',
            [$this, 'rawurlencodeMatchZero'],
            $path
        );
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
            '/(?:[^' . self::$CHAR_UNRESERVED . self::$CHAR_SUB_DELIMS . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
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
