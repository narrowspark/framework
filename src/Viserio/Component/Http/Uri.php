<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use InvalidArgumentException;
use League\Uri\Schemes\Http as HttpUri;
use Psr\Http\Message\UriInterface;
use Viserio\Component\Http\Uri\Filter\Fragment;
use Viserio\Component\Http\Uri\Filter\Host;
use Viserio\Component\Http\Uri\Filter\Path;
use Viserio\Component\Http\Uri\Filter\Port;
use Viserio\Component\Http\Uri\Filter\Query;
use Viserio\Component\Http\Uri\Filter\Scheme;

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

        $this->scheme = $this->formatScheme($components['scheme']);
        $this->user_info = $this->formatUserInfo($components['user'], $components['pass']);
        $this->host = $this->formatHost($components['host']);
        $this->port = $this->formatPort($components['port']);
        $this->authority = $this->setAuthority();
        $this->path = $this->filterPath($components['path']);
        $this->query = $this->formatQueryAndFragment($components['query']);
        $this->fragment = $this->formatQueryAndFragment($components['fragment']);

        $this->assertValidState();
    }
}
