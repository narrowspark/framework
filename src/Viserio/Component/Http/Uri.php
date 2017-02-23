<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use League\Uri\Schemes\AbstractUri;
use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Schemes\UriException;
use Psr\Http\Message\UriInterface;

class Uri extends HttpUri implements UriInterface
{
    /**
     * Create a new uri instance.
     *
     * @param string $uri
     */
    public function __construct(string $uri = '')
    {
        $components = self::getParser()(self::filterString($uri));

        $this->scheme    = $this->formatScheme($components['scheme']);
        $this->user_info = $this->formatUserInfo($components['user'], $components['pass']);
        $this->host      = $this->formatHost($components['host']);
        $this->port      = $this->formatPort($components['port']);
        $this->authority = $this->setAuthority();
        $this->path      = $this->filterPath($components['path']);
        $this->query     = $this->formatQueryAndFragment($components['query']);
        $this->fragment  = $this->formatQueryAndFragment($components['fragment']);

        $this->assertValidState();
    }

    /**
     * {@inheritdoc}
     */
    public static function __set_state(array $components): AbstractUri
    {
        $user_info          = explode(':', $components['user_info'], 2);
        $components['user'] = array_shift($user_info);
        $components['pass'] = array_shift($user_info);

        return (new static())
            ->withHost($components['host'])
            ->withScheme($components['scheme'])
            ->withUserInfo($components['user'], $components['pass'])
            ->withPort(self::$supported_schemes[$components['scheme']] ?? null)
            ->withPath($components['path'])
            ->withQuery($components['query'])
            ->withFragment($components['fragment']);
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromString(string $uri = ''): AbstractUri
    {
        $components = self::getParser()(self::filterString($uri));

        return (new static())
            ->withScheme($components['scheme'])
            ->withHost($components['host'])
            ->withUserInfo($components['user'], $components['pass'])
            ->withPort(self::$supported_schemes[$components['scheme']] ?? null)
            ->withPath($components['path'])
            ->withQuery($components['query'])
            ->withFragment($components['fragment']);
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromServer(array $server): HttpUri
    {
        list($user, $pass)  = static::fetchUserInfo($server);
        list($host, $port)  = static::fetchHostname($server);
        list($path, $query) = static::fetchRequestUri($server);
        $scheme             = static::fetchScheme($server);

        return (new static())
            ->withHost($host)
            ->withScheme($scheme)
            ->withPort(self::$supported_schemes[$scheme] ?? null)
            ->withUserInfo($user, $pass)
            ->withPath($path)
            ->withQuery($query !== null ? $query : '');
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromComponents(array $components): AbstractUri
    {
        $components += [
            'scheme' => null, 'user' => null, 'pass' => null, 'host' => null,
            'port'   => null, 'path' => '', 'query' => null, 'fragment' => null,
        ];

        if (null !== $components['host'] && ! self::getParser()->isHost($components['host'])) {
            throw UriException::createFromInvalidHost($components['host']);
        }

        return (new static())
            ->withHost($components['host'])
            ->withScheme($components['scheme'])
            ->withUserInfo($components['user'], $components['pass'])
            ->withPort(self::$supported_schemes[$components['scheme']] ?? null)
            ->withPath($components['path'])
            ->withQuery($components['query'])
            ->withFragment($components['fragment']);
    }
}
