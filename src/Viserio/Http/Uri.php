<?php
declare(strict_types=1);
namespace Viserio\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Viserio\Http\Uri\Filter\Fragment;
use Viserio\Http\Uri\Filter\Host;
use Viserio\Http\Uri\Filter\Path;
use Viserio\Http\Uri\Filter\Port;
use Viserio\Http\Uri\Filter\Query;
use Viserio\Http\Uri\Filter\Scheme;

class Uri implements UriInterface
{
    /**
     * Absolute http and https URIs require a host per RFC 7230 Section 2.7
     * but in generic URIs the host can be empty. So for http(s) URIs
     * we apply this default host when no host is given yet to form a
     * valid URI.
     */
    const HTTP_DEFAULT_HOST = 'localhost';

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
     * The host data.
     *
     * @var array
     */
    protected $data;

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
     * All filter.
     *
     * @var array
     */
    private $filterClass = [];

    /**
     * Create a new uri instance.
     *
     * @param string $url
     */
    public function __construct(string $url = '')
    {
        $this->filterClass = [
            'fragment' => new Fragment(),
            'host' => new Host(),
            'path' => new Path(),
            'port' => new Port(),
            'scheme' => new Scheme(),
            'query' => new Query(),
        ];

        if ($url !== '') {
            $this->createFromComponents($this->utf8UrlParser($url));
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
    public function withScheme($scheme): UriInterface
    {
        $this->isValidString($scheme);

        $scheme = $this->filterClass['scheme']->filter($scheme);

        if ($this->scheme === $scheme) {
            return $this;
        }

        $new = clone $this;
        $new->scheme = $scheme;
        $new->port = $this->filterClass['port']->filter($new->scheme, $new->port);
        $new->validateState();

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withUserInfo($user, $password = null): UriInterface
    {
        $this->isValidString($user);

        $info = $user;

        if ($password != '') {
            $this->isValidString($password);

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
    public function withHost($host): UriInterface
    {
        $this->isValidString($host);

        $host = $this->filterClass['host']->filter($host);

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
    public function withPort($port): UriInterface
    {
        $port = $this->filterClass['port']->filter($this->scheme, $port);

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
    public function withPath($path): UriInterface
    {
        $this->isValidString($path);

        $path = $this->filterClass['path']->filter($path);

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
    public function withQuery($query): UriInterface
    {
        $this->isValidString($query);

        if ($this->query === $query) {
            return $this;
        }

        $filter = $this->filterClass['query'];

        $new = clone $this;
        $new->queryVars = $filter->parse($query);
        $new->query = $filter->build($new->queryVars);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withFragment($fragment): UriInterface
    {
        $this->isValidString($fragment);

        $fragment = $this->filterClass['fragment']->filter($fragment);

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
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        $authority = $this->host;

        if ($this->userInfo !== '') {
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
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        // If the query is empty build it first
        if (is_null($this->query)) {
            $this->query = $this->filterClass['query']->build($this->queryVars);
        }

        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components a hash representation of the URI similar to PHP parse_url function result
     *
     * @return void
     */
    private function createFromComponents(array $components): void
    {
        $queryFilter = $this->filterClass['query'];

        // Parse the query
        if (isset($components['query'])) {
            $this->queryVars = $queryFilter->parse($components['query']);
            $this->query = $queryFilter->build($this->queryVars);
        } else {
            $this->query = '';
        }

        $this->scheme = isset($components['scheme']) ? $this->filterClass['scheme']->filter($components['scheme']) : '';
        $this->userInfo = $components['user'] ?? '';
        $this->host = isset($components['host']) ? $this->filterClass['host']->filter($components['host']) : '';
        $this->port = isset($components['port']) ? $this->filterClass['port']->filter($this->scheme, $components['port']) : null;
        $this->path = isset($components['path']) ? $this->filterClass['path']->filter($components['path']) : '';

        $this->fragment = isset($components['fragment']) ? $this->filterClass['fragment']->filter($components['fragment']) : '';

        if (isset($components['pass']) != '') {
            $this->userInfo .= ':' . $components['pass'];
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
     * Validate if is string.
     *
     * @param string|null $string
     */
    private function isValidString($string): ?string
    {
        if (! is_string($string)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s',
                __METHOD__,
                (is_object($string) ? get_class($string) : gettype($string))
            ));
        }
    }

    /**
     * Validate uri state.
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    private function validateState(): void
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

            if ($this->scheme === '' && strpos(explode('/', $this->path, 2)[0], ':') !== false) {
                throw new InvalidArgumentException(
                    'A relative URI must not have a path beginning with a segment containing a colon'
                );
            }
        } elseif (isset($this->path[0]) && $this->path[0] !== '/') {
            throw new InvalidArgumentException(
                'The path of a URI with an authority must start with a slash "/" or be empty'
            );
        }
    }

    /**
     * Parse urls with utf-8 support.
     *
     * @param string $url
     *
     * @return array
     */
    private function utf8UrlParser(string $url): array
    {
        $encodeUrl = preg_replace_callback(
            '%[^:/@?&=#]+%usD',
            function ($matches) {
                return urlencode($matches[0]);
            },
            $url
        );

        $components = parse_url($encodeUrl);

        if (! $components) {
            throw new InvalidArgumentException(sprintf('Unable to parse URI: %s.', $url));
        }

        foreach ($components as $key => $value) {
            $components[$key] = urldecode((string) $value);
        }

        return $components;
    }
}
