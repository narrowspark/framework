<?php
namespace Viserio\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri extends UriHelper implements UriInterface
{
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
            $this->createFromComponents($this->parseUri($uri));
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
        $this->host = isset($components['host']) ? $this->filterHost($components['host']) : '';
        $this->port = isset($components['port']) ? $this->filterPort($components['port']) : null;
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
}
