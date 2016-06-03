<?php
namespace Viserio\Http\Tests;

use Viserio\Http\Uri;
use Psr\Http\Message\UriInterface;
use Viserio\Http\Tests\Constraint\Immutable;

class UriTest extends \PHPUnit_Framework_TestCase
{
    public function createDefaultUri()
    {
        return new Uri();
    }

    public function testUriImplementsInterface()
    {
        $this->assertInstanceOf(UriInterface::class, $this->createDefaultUri());
    }

    public function testGetSchemeReturnsString()
    {
        $uri = $this->createDefaultUri();
        $this->assertInternalType('string', $uri->getScheme(), 'getScheme returns a string');
    }

    /**
     * @dataProvider schemeProvider
     */
    public function testGetScheme($scheme, $expected)
    {
        $uri = $this->createDefaultUri();
        $uriClone = clone $uri;
        $newUri = $uri->withScheme($scheme);
        $this->assertImmutable($uri, $uriClone, $newUri);
        $this->assertEquals($expected, $newUri->getScheme(), 'Scheme must be normalized according to RFC3986');
    }

    public function schemeProvider()
    {
        return [
            'normalized scheme' => ['HtTpS', 'https'],
            'simple scheme' => ['http',  'http'],
            'no scheme' => ['', ''],
        ];
    }

    /**
     * @param UriInterface $uriClone
     * @param UriInterface $uri
     * @param UriInterface $newUri
     *
     * @return void
     */
    protected function assertImmutable($uri, $uriClone, $newUri)
    {
        $this->assertEquals($uriClone, $uri, 'Original URI must be immutable');
        Immutable::assertImmutable($uri, $newUri);
    }

    public function testGetUserInfoReturnsString()
    {
        $uri = $this->createDefaultUri();
        $this->assertInternalType('string', $uri->getUserInfo(), 'getUserInfo returns a string');
    }

    /**
     * @dataProvider userInfoProvider
     */
    public function testGetUserInfo($user, $pass, $expected)
    {
        $uri = $this->createDefaultUri();
        $uriClone = clone $uri;
        $newUri = $uri->withUserInfo($user, $pass);
        $this->assertImmutable($uri, $uriClone, $newUri);
        $this->assertEquals($expected, $newUri->getUserInfo());
    }

    public function userInfoProvider()
    {
        return [
            'with userinfo' => ['iGoR', 'rAsMuZeN', 'iGoR:rAsMuZeN'],
            'no userinfo' => ['', '', ''],
            'no pass' => ['iGoR', '', 'iGoR'],
            'pass is null' => ['iGoR', null, 'iGoR'],
        ];
    }

    public function testGetHostReturnsString()
    {
        $uri = $this->createDefaultUri();
        $this->assertInternalType('string', $uri->getHost(), 'getHost returns a string');
    }

    /**
     * @dataProvider hostProvider
     *
     * Host MUST be normalized to lowercase if present
     */
    public function testGetHost($host, $expected)
    {
        $uri = $this->createDefaultUri();
        $uriClone = clone $uri;
        $newUri = $uri->withHost($host);
        $this->assertImmutable($uri, $uriClone, $newUri);
        $this->assertEquals($expected, $newUri->getHost(), 'Host must be normalized according to RFC3986');
    }

    public function hostProvider()
    {
        return [
            'normalized host' => ['MaStEr.eXaMpLe.CoM', 'master.example.com'],
            'simple host' => ['www.example.com', 'www.example.com'],
            'IDN hostname' => ['مثال.إختبار', 'مثال.إختبار'],
            'IPv6 Host' => ['[::1]', '[::1]'],
        ];
    }

    public function testGetPortDefaultReturnsNull()
    {
        $uri = $this->createDefaultUri();
        $this->assertInternalType('null', $uri->getPort(), 'getPort returns null');
    }

    /**
     * @dataProvider portProvider
     *
     * If no port is present and no scheme is present, this method MUST return a null value
     * If no port is present but a scheme is present, this method MAY return the standard port, but SHOULD return null
     */
    public function testGetPort($port, $scheme, $host, $expected)
    {
        $uri = $this->createDefaultUri();
        $uriClone = clone $uri;
        $newUri = $uri->withHost($host)->withScheme($scheme)->withPort($port);
        $this->assertImmutable($uri, $uriClone, $newUri);
        $this->assertEquals($expected, $newUri->getPort(), 'port must be an int or null');
    }

    public function portProvider()
    {
        return [
            'non standard string port' => ['443', 'http', 'localhost', 443],
            'non standard int port' => [443, 'http', 'localhost', 443],
            'no port' => [null, 'http', 'localhost', null],
            'standard port' => [80, 'http', 'localhost', null],
        ];
    }

    public function testGetAuthorityReturnsString()
    {
        $uri = $this->createDefaultUri();
        $this->assertInternalType('string', $uri->getAuthority(), 'getAuthority returns a string');
    }

    /**
     * @dataProvider authorityProvider
     *
     * If the port component is not set or is the standard port for the current
     * scheme, it SHOULD NOT be included.
     */
    public function testGetAuthority($scheme, $user, $pass, $host, $port, $authority)
    {
        $uri = $this->createDefaultUri();
        $uriClone = clone $uri;
        $newUri = $uri
                ->withHost($host)
                ->withScheme($scheme)
                ->withUserInfo($user, $pass)
                ->withPort($port);
        $this->assertImmutable($uri, $uriClone, $newUri);
        $this->assertEquals($authority, $newUri->getAuthority());
    }

    public function authorityProvider()
    {
        return [
            'authority' => [
                'scheme' => 'http',
                'user' => 'iGoR',
                'pass' => 'rAsMuZeN',
                'host' => 'master.example.com',
                'port' => 443,
                'authority' => 'iGoR:rAsMuZeN@master.example.com:443',
            ],
            'without port' => [
                'scheme' => 'http',
                'user' => 'iGoR',
                'pass' => 'rAsMuZeN',
                'host' => 'master.example.com',
                'port' => null,
                'authority' => 'iGoR:rAsMuZeN@master.example.com',
            ],
            'with standard port' => [
                'scheme' => 'http',
                'user' => 'iGoR',
                'pass' => 'rAsMuZeN',
                'host' => 'master.example.com',
                'port' => 80,
                'authority' => 'iGoR:rAsMuZeN@master.example.com',
            ],
            'authority without pass' => [
                'scheme' => 'http',
                'user' => 'iGoR',
                'pass' => '',
                'host' => 'master.example.com',
                'port' => null,
                'authority' => 'iGoR@master.example.com',
            ],
            'authority without port and userinfo' => [
                'scheme' => 'http',
                'user' => '',
                'pass' => '',
                'host' => 'master.example.com',
                'port' => null,
                'authority' => 'master.example.com',
            ],
        ];
    }

    public function testGetPathReturnsString()
    {
        $uri = $this->createDefaultUri();
        $this->assertInternalType('string', $uri->getPath(), 'getPath returns a string');
    }

    /**
     * @dataProvider pathProvider
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     */
    public function testGetPath($path, $expected)
    {
        $uri = $this->createDefaultUri();
        $uriClone = clone $uri;
        $newUri = $uri->withPath($path);
        $this->assertImmutable($uri, $uriClone, $newUri);
        $this->assertEquals($expected, $newUri->getPath(), 'Path must be normalized according to RFC3986');
    }

    public function pathProvider()
    {
        return [
            'normalized path' => ['/%7ejohndoe/%a1/index.php', '/~johndoe/%A1/index.php'],
            'slash forward only path' => ['/', '/'],
            'empty path' => ['', ''],
        ];
    }

    public function testGetQueryReturnsString()
    {
        $uri = $this->createDefaultUri();
        $this->assertInternalType('string', $uri->getQuery(), 'getQuery returns a string');
    }

    /**
     * @dataProvider queryProvider
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     */
    public function testGetQuery($query, $expected)
    {
        $uri = $this->createDefaultUri();
        $uriClone = clone $uri;
        $newUri = $uri->withQuery($query);
        $this->assertImmutable($uri, $uriClone, $newUri);
        $this->assertEquals($expected, $newUri->getQuery(), 'Query must be normalized according to RFC3986');
    }

    public function queryProvider()
    {
        return [
            'normalized query' => ['foo.bar=%7evalue', 'foo.bar=~value'],
            'empty query' => ['', ''],
            'same param query' => ['foo.bar=1&foo.bar=1', 'foo.bar=1&foo.bar=1'],
            'query with delimiter' => ['?foo=1', '%3Ffoo=1'],
        ];
    }

    public function testGetFragmentReturnsString()
    {
        $uri = $this->createDefaultUri();
        $this->assertInternalType('string', $uri->getFragment(), 'getFragment returns a string');
    }

    /**
     * @dataProvider fragmentProvider
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.5.
     */
    public function testGetFragment($fragment, $expected)
    {
        $uri = $this->createDefaultUri();
        $uriClone = clone $uri;
        $newUri = $uri->withFragment($fragment);
        $this->assertImmutable($uri, $uriClone, $newUri);
        $this->assertEquals($expected, $newUri->getFragment(), 'Fragment must be normalized according to RFC3986');
    }

    public function fragmentProvider()
    {
        return [
            'simple fragment' => ['fragment', 'fragment'],
            'fragment with delimiter' => ['#fragment', '%23fragment'],
            'URI with non-encodable fragment' => ["azAZ0-9/?-._~!$&'()*+,;=:@", "azAZ0-9/?-._~!$&'()*+,;=:@"],
        ];
    }

    /**
     * @dataProvider stringProvider
     *
     * - If a scheme is present, it MUST be suffixed by ":".
     * - If an authority is present, it MUST be prefixed by "//".
     * - The path can be concatenated without delimiters. But there are two
     *   cases where the path has to be adjusted to make the URI reference
     *   valid as PHP does not allow to throw an exception in __toString():
     *     - If the path is rootless and an authority is present, the path MUST
     *       be prefixed by "/".
     *     - If the path is starting with more than one "/" and no authority is
     *       present, the starting slashes MUST be reduced to one.
     * - If a query is present, it MUST be prefixed by "?".
     * - If a fragment is present, it MUST be prefixed by "#".
     */
    public function testToString($scheme, $user, $pass, $host, $port, $path, $query, $fragment, $expected)
    {
        $uri = $this->createDefaultUri();
        $uriClone = clone $uri;
        $newUri = $uri
                ->withHost($host)
                ->withScheme($scheme)
                ->withUserInfo($user, $pass)
                ->withPort($port)
                ->withPath($path)
                ->withQuery($query)
                ->withFragment($fragment);
        $this->assertImmutable($uri, $uriClone, $newUri);
        $this->assertEquals($expected, (string) $newUri, 'URI string must be normalized according to RFC3986 rules');
    }

    public function stringProvider()
    {
        return [
            'URI normalized' => [
                'scheme' => 'HtTps',
                'user' => 'iGoR',
                'pass' => 'rAsMuZeN',
                'host' => 'MaStEr.eXaMpLe.CoM',
                'port' => 443,
                'path' => '/%7ejohndoe/%a1/index.php',
                'query' => 'foo.bar=%7evalue',
                'fragment' => 'fragment',
                'uri' => 'https://iGoR:rAsMuZeN@master.example.com/~johndoe/%A1/index.php?foo.bar=~value#fragment',
            ],
            'URI without scheme' => [
                'scheme' => '',
                'user' => '',
                'pass' => '',
                'host' => 'www.example.com',
                'port' => 443,
                'path' => '/foo/bar',
                'query' => 'param=value',
                'fragment' => 'fragment',
                'uri' => '//www.example.com:443/foo/bar?param=value#fragment',
            ],
            'URI with rootless path' => [
                'scheme' => 'http',
                'user' => '',
                'pass' => '',
                'host' => 'www.example.com',
                'port' => null,
                'path' => 'foo/bar',
                'query' => '',
                'fragment' => '',
                'uri' => 'http://www.example.com/foo/bar',
            ],
            'URI without scheme and authority' => [
                'scheme' => '',
                'user' => '',
                'pass' => '',
                'host' => '',
                'port' => null,
                'path' => '//foo/bar',
                'query' => '',
                'fragment' => '',
                'uri' => '/foo/bar',
            ],
        ];
    }

    /**
     * @dataProvider withSchemeFailedProvider
     * @expectedException InvalidArgumentException
     */
    public function testWithSchemeFailed($scheme)
    {
        $this->createDefaultUri()->withScheme($scheme);
    }

    public function withSchemeFailedProvider()
    {
        return [
            'invalid char' => ['in,valid'],
            'integer like string' => ['123'],
            'float' => [1.2],
            'array' => [['foo']],
            'unknown scheme' => ['yolo'],
        ];
    }

    /**
     * @dataProvider withUserInfoFailedProvider
     * @expectedException InvalidArgumentException
     */
    public function testWithUserInfoFailed($user, $pass)
    {
        $this->createDefaultUri()->withUserInfo($user, $pass);
    }

    public function withUserInfoFailedProvider()
    {
        return [
            'invalid character in user :' => ['igo:r', 'rAsMuZeN'],
            'invalid character in user @' => ['igo@r', 'rAsMuZeN'],
            'invalid character in pass' => ['iGoR', 'rasmu@sen'],
            'array in user' => [['iGoR'], 'rAsMuZeN'],
            'array in pass' => ['iGoR', ['rAsMuZeN']],
        ];
    }

    /**
     * @dataProvider withHostFailedProvider
     * @expectedException InvalidArgumentException
     */
    public function testWithHostFailed($host)
    {
        $this->createDefaultUri()->withHost($host);
    }

    public function withHostFailedProvider()
    {
        return [
            'dot in front' => ['.example.com'],
            'hyphen suffix' => ['host.com-'],
            'multiple dot' => ['.......'],
            'one dot' => ['.'],
            'empty label' => ['tot.    .coucou.com'],
            'space in the label' => ['re view'],
            'underscore in label' => ['_bad.host.com'],
            'label too long' => [implode('', array_fill(0, 12, 'banana')) . '.secure.example.com'],
            'too many labels' => [implode('.', array_fill(0, 128, 'a'))],
            'Invalid IP format' => ['[127.0.0.1]'],
            'Invalid IPv6 format' => ['[[::1]]'],
            'Incomplete IPv6 format' => ['[::1'],
            'space character in starting label' => ['example. com'],
            'invalid character in host label' => ["examp\0le.com"],
            'invalid IP with scope' => ['[127.2.0.1%253]'],
            'invalid scope IPv6' => ['[ab23::1234%251]'],
            'invalid scope ID' => ['[fe80::1234%25?@]'],
            'invalid scope ID with utf8 character' => ['[fe80::1234%25€]'],
        ];
    }

    /**
     * @dataProvider withPortFailedProvider
     * @expectedException InvalidArgumentException
     */
    public function testWithPortFailed($port)
    {
        $this->createDefaultUri()->withPort($port);
    }

    public function withPortFailedProvider()
    {
        return [
            'string' => ['toto'],
            'invalid port number too low' => [0],
            'invalid port number too high' => [65536],
            'float' => [1.2],
        ];
    }

    /**
     * @dataProvider withPathFailedProvider
     * @expectedException InvalidArgumentException
     */
    public function withPathFailed($path)
    {
        $this->createDefaultUri()->withPath($path);
    }

    public function withPathFailedProvider()
    {
        return [
            'invalid ? character' => ['foo?bar'],
            'invalid # character' => ['foo#bar'],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function withQueryFailed()
    {
        $this->createDefaultUri()->withQuery('bar#toto');
    }
}
