<?php
namespace Viserio\Http;

use League\Uri\Components\Fragment;
use League\Uri\Components\HierarchicalPath as Path;
use League\Uri\Components\Host;
use League\Uri\Components\Pass;
use League\Uri\Components\Port;
use League\Uri\Components\Query;
use League\Uri\Components\Scheme;
use League\Uri\Components\User;
use League\Uri\Components\UserInfo;
use League\Uri\Interfaces\Fragment as FragmentInterface;
use League\Uri\Interfaces\HierarchicalPath as PathInterface;
use League\Uri\Interfaces\Host as HostInterface;
use League\Uri\Interfaces\Port as PortInterface;
use League\Uri\Interfaces\Query as QueryInterface;
use League\Uri\Interfaces\Scheme as SchemeInterface;
use League\Uri\Interfaces\UserInfo as UserInfoInterface;
use League\Uri\Schemes\Generic\AbstractUri;
use League\Uri\UriParser;
use Psr\Http\Message\UriInterface;

class Uri extends AbstractUri implements UriInterface
{
    /**
     * @param string $uri
     */
    public function __construct(string $uri = '')
    {
        if (! empty($uri)) {
            $this->parseUri($uri);
        }

        $this->assertValidObject();
    }

    /**
     * Parse a URI into its parts, and set the properties
     *
     * @param string $uri
     */
    private function parseUri($uri)
    {
        $this->createFromComponents((new UriParser())->__invoke($uri));
    }

    /**
     * Create a new instance from a hash of parse_url parts
     *
     * @param array $components a hash representation of the URI similar to PHP parse_url function result
     *
     * @return static
     */
    private function createFromComponents(array $components)
    {
        $components = self::normalizeUriHash($components);

        $this->scheme = (string) new Scheme($components['scheme']);
        $this->userInfo = (string) new UserInfo(new User($components['user']), new Pass($components['pass']));
        $this->host = (string) new Host($components['host']);
        $this->port = (string) new Port($components['port']);
        $this->path = (string) new Path($components['path']);
        $this->query = (string) new Query($components['query']);
        $this->fragment = (string) new Fragment($components['fragment']);
    }

    /**
     * Tell whether the current URI is valid.
     *
     * The URI object validity depends on the scheme. This method
     * MUST be implemented on every URI object
     *
     * @return bool
     */
    protected function isValid()
    {
        return $this->isValidGenericUri()
            && $this->isValidHierarchicalUri();
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
}
