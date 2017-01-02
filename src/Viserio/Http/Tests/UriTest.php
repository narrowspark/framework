<?php
declare(strict_types=1);
namespace Viserio\Http\Tests;

use Psr\Http\Message\UriInterface;
use Viserio\Http\Tests\Fixture\ExtendedUriTest;
use Viserio\Http\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    public const RFC3986_BASE = 'http://a/b/c/d;p?q';

    public function createDefaultUri()
    {
        return new Uri();
    }

    public function testUriImplementsInterface()
    {
        self::assertInstanceOf(UriInterface::class, new Uri());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidURIProvider
     *
     * @param string $uri
     */
    public function testParseFailed($uri)
    {
        new Uri($uri);
    }

    public function invalidURIProvider()
    {
        return [
            'invalid uri'                                  => ['///'],
            'invalid uri no host'                          => ['http:///example.com'],
            'invalid uri no host with port'                => ['http://:80'],
            'invalid uri no host with port and wrong auth' => ['http://user@:80'],
        ];
    }

    public function testParsesProvidedUri()
    {
        $uri = new Uri('https://user:pass@example.com:8080/path/123?q=abc#test');

        self::assertSame('https', $uri->getScheme());
        self::assertSame('user:pass@example.com:8080', $uri->getAuthority());
        self::assertSame('user:pass', $uri->getUserInfo());
        self::assertSame('example.com', $uri->getHost());
        self::assertSame(8080, $uri->getPort());
        self::assertSame('/path/123', $uri->getPath());
        self::assertSame('q=abc', $uri->getQuery());
        self::assertSame('test', $uri->getFragment());
        self::assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string) $uri);
    }

    public function testCanTransformAndRetrievePartsIndividually()
    {
        $uri = $this->createDefaultUri()
            ->withScheme('https')
            ->withUserInfo('user', 'pass')
            ->withHost('example.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('q=abc')
            ->withFragment('test');

        self::assertSame('https', $uri->getScheme());
        self::assertSame('user:pass@example.com:8080', $uri->getAuthority());
        self::assertSame('user:pass', $uri->getUserInfo());
        self::assertSame('example.com', $uri->getHost());
        self::assertSame(8080, $uri->getPort());
        self::assertSame('/path/123', $uri->getPath());
        self::assertSame('q=abc', $uri->getQuery());
        self::assertSame('test', $uri->getFragment());
        self::assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string) $uri);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getInvalidUris
     *
     * @param mixed $invalidUri
     */
    public function testInvalidUrisThrowException($invalidUri)
    {
        new Uri($invalidUri);
    }

    public function getInvalidUris()
    {
        return [
            // parse_url() requires the host component which makes sense for http(s)
            // but not when the scheme is not known or different. So '//' or '///' is
            // currently invalid as well but should not according to RFC 3986.
            ['http://'],
            ['urn://host:with:colon'], // host cannot contain ":"
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid port: 100000. Must be between 1 and 65535
     */
    public function testPortMustBeValid()
    {
        $this->createDefaultUri()->withPort(100000);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid port: 0. Must be between 1 and 65535
     */
    public function testWithPortCannotBeZero()
    {
        $this->createDefaultUri()->withPort(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseUriPortCannotBeZero()
    {
        new Uri('//example.com:0');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSchemeMustHaveCorrectType()
    {
        $this->createDefaultUri()->withScheme([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHostMustHaveCorrectType()
    {
        $this->createDefaultUri()->withHost([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPathMustHaveCorrectType()
    {
        $this->createDefaultUri()->withPath([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testQueryMustHaveCorrectType()
    {
        $this->createDefaultUri()->withQuery([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFragmentMustHaveCorrectType()
    {
        $this->createDefaultUri()->withFragment([]);
    }

    public function testSchemeIsNormalizedToLowercase()
    {
        $uri = new Uri('HTTP://example.com');

        self::assertSame('http', $uri->getScheme());
        self::assertSame('http://example.com', (string) $uri);

        $uri = (new Uri('//example.com'))->withScheme('HTTP');

        self::assertSame('http', $uri->getScheme());
        self::assertSame('http://example.com', (string) $uri);
    }

    public function testPortIsNullIfStandardPortForScheme()
    {
        // HTTPS standard port
        $uri = new Uri('https://example.com:443');

        self::assertNull($uri->getPort());
        self::assertSame('example.com', $uri->getAuthority());

        $uri = (new Uri('https://example.com'))->withPort(443);

        self::assertNull($uri->getPort());
        self::assertSame('example.com', $uri->getAuthority());

        // HTTP standard port
        $uri = new Uri('http://example.com:80');

        self::assertNull($uri->getPort());
        self::assertSame('example.com', $uri->getAuthority());

        $uri = (new Uri('http://example.com'))->withPort(80);

        self::assertNull($uri->getPort());
        self::assertSame('example.com', $uri->getAuthority());
    }

    public function testPortIsReturnedIfSchemeUnknown()
    {
        $uri = (new Uri('//example.com'))->withPort(80);

        self::assertSame(80, $uri->getPort());
        self::assertSame('example.com:80', $uri->getAuthority());
    }

    public function testStandardPortIsNullIfSchemeChanges()
    {
        $uri = new Uri('http://example.com:443');

        self::assertSame('http', $uri->getScheme());
        self::assertSame(443, $uri->getPort());

        $uri = $uri->withScheme('https');

        self::assertNull($uri->getPort());
    }

    public function testPortPassedAsStringIsCastedToInt()
    {
        $uri = (new Uri('//example.com'))->withPort('8080');

        self::assertSame(8080, $uri->getPort(), 'Port is returned as integer');
        self::assertSame('example.com:8080', $uri->getAuthority());
    }

    public function testPortCanBeRemoved()
    {
        $uri = (new Uri('http://example.com:8080'))->withPort(null);

        self::assertNull($uri->getPort());
        self::assertSame('http://example.com', (string) $uri);
    }

    /**
     * In RFC 8986 the host is optional and the authority can only
     * consist of the user info and port.
     */
    public function testAuthorityWithUserInfoOrPortButWithoutHost()
    {
        $uri = $this->createDefaultUri()->withUserInfo('user', 'pass');

        self::assertSame('user:pass', $uri->getUserInfo());
        self::assertSame('user:pass@', $uri->getAuthority());

        $uri = $uri->withPort(8080);

        self::assertSame(8080, $uri->getPort());
        self::assertSame('user:pass@:8080', $uri->getAuthority());
        self::assertSame('//user:pass@:8080', (string) $uri);

        $uri = $uri->withUserInfo('');

        self::assertSame(':8080', $uri->getAuthority());
    }

    /**
     * @dataProvider userInfoProvider
     * @group userinfo
     *
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $expected
     */
    public function testGetUserInfo($user, $pass, $expected)
    {
        $uri = $this->createDefaultUri()->withUserInfo($user, $pass);

        self::assertEquals($expected, $uri->getUserInfo());
    }

    public function userInfoProvider()
    {
        return [
            'with userinfo' => ['iGoR', 'rAsMuZeN', 'iGoR:rAsMuZeN'],
            'no userinfo'   => ['', '', ''],
            'no pass'       => ['iGoR', '', 'iGoR'],
            'pass is null'  => ['iGoR', null, 'iGoR'],
            'upercased'     => ['IgOr', 'RaSm0537', 'IgOr:RaSm0537'],
        ];
    }

    public function testHostInHttpUriDefaultsToLocalhost()
    {
        $uri = $this->createDefaultUri()->withScheme('http');

        self::assertSame('localhost', $uri->getHost());
        self::assertSame('localhost', $uri->getAuthority());
        self::assertSame('http://localhost', (string) $uri);
    }

    public function testHostInHttpsUriDefaultsToLocalhost()
    {
        $uri = $this->createDefaultUri()->withScheme('https');

        self::assertSame('localhost', $uri->getHost());
        self::assertSame('localhost', $uri->getAuthority());
        self::assertSame('https://localhost', (string) $uri);
    }

    public function uriComponentsEncodingProvider()
    {
        $unreserved = 'a-zA-Z0-9.-_~!$&\'()*+,;=:@';

        return [
            // Percent encode spaces
            ['/pa th?q=va lue#frag ment', '/pa%20th', 'q=va%20lue', 'frag%20ment', '/pa%20th?q=va%20lue#frag%20ment'],
            // Percent encode multibyte
            ['/€?€#€', '/%E2%82%AC', '%E2%82%AC', '%E2%82%AC', '/%E2%82%AC?%E2%82%AC#%E2%82%AC'],
            // Don't encode something that's already encoded
            ['/pa%20th?q=va%20lue#frag%20ment', '/pa%20th', 'q=va%20lue', 'frag%20ment', '/pa%20th?q=va%20lue#frag%20ment'],
            // Percent encode invalid percent encodings
            ['/pa%2-th?q=va%2-lue#frag%2-ment', '/pa%252-th', 'q=va%252-lue', 'frag%252-ment', '/pa%252-th?q=va%252-lue#frag%252-ment'],
            // Don't encode path segments
            ['/pa/th//two?q=va/lue#frag/ment', '/pa/th//two', 'q=va/lue', 'frag/ment', '/pa/th//two?q=va/lue#frag/ment'],
            // Don't encode unreserved chars or sub-delimiters
            ["/$unreserved?$unreserved#$unreserved", "/$unreserved", $unreserved, $unreserved, "/$unreserved?$unreserved#$unreserved"],
            // Encoded unreserved chars are not decoded
            ['/p%61th?q=v%61lue#fr%61gment', '/p%61th', 'q=v%61lue', 'fr%61gment', '/p%61th?q=v%61lue#fr%61gment'],
        ];
    }

    /**
     * @dataProvider uriComponentsEncodingProvider
     *
     * @param mixed $input
     * @param mixed $path
     * @param mixed $query
     * @param mixed $fragment
     * @param mixed $output
     */
    public function testUriComponentsGetEncodedProperly($input, $path, $query, $fragment, $output)
    {
        $uri = new Uri($input);

        self::assertSame($path, $uri->getPath());
        self::assertSame($query, $uri->getQuery());
        self::assertSame($fragment, $uri->getFragment());
        self::assertSame($output, (string) $uri);
    }

    public function testWithPathEncodesProperly()
    {
        $uri = $this->createDefaultUri()->withPath('/baz?#€/b%61r^bar');

        // Query and fragment delimiters and multibyte chars are encoded.
        self::assertSame('/baz%3F%23%E2%82%AC/b%61r%5Ebar', $uri->getPath());
        self::assertSame('/baz%3F%23%E2%82%AC/b%61r%5Ebar', (string) $uri);
    }

    public function testWithQueryEncodesProperly()
    {
        $uri = $this->createDefaultUri()->withQuery('?=#&€=/&b%61r');

        // A query starting with a "?" is valid and must not be magically removed. Otherwise it would be impossible to
        // construct such an URI. Also the "?" and "/" does not need to be encoded in the query.
        self::assertSame('?=%23&%E2%82%AC=/&b%61r', $uri->getQuery());
        self::assertSame('??=%23&%E2%82%AC=/&b%61r', (string) $uri);
    }

    public function testWithFragmentEncodesProperly()
    {
        $uri = $this->createDefaultUri()->withFragment('#€?/b%61r');

        // A fragment starting with a "#" is valid and must not be magically removed. Otherwise it would be impossible to
        // construct such an URI. Also the "?" and "/" does not need to be encoded in the fragment.
        self::assertSame('%23%E2%82%AC?/b%61r', $uri->getFragment());
        self::assertSame('#%23%E2%82%AC?/b%61r', (string) $uri);
    }

    public function testAllowsForRelativeUri()
    {
        $uri = $this->createDefaultUri()->withPath('foo');

        self::assertSame('foo', $uri->getPath());
        self::assertSame('foo', (string) $uri);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPathStartingWithTwoSlashes()
    {
        $uri = new Uri('http://example.org//path-not-host.com');

        self::assertSame('//path-not-host.com', $uri->getPath());

        $uri = $uri->withScheme('');

        self::assertSame('//example.org//path-not-host.com', (string) $uri); // This is still valid

        $uri->withHost(''); // Now it becomes invalid
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The path of a URI with an authority must start with a slash "/" or be empty
     */
    public function testRelativePathAndAuhorityIsInvalid()
    {
        // concatenating a relative path with a host doesn't work: "//example.comfoo" would be wrong
        $this->createDefaultUri()->withPath('foo')->withHost('example.com');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The path of a URI without an authority must not start with two slashes "//"
     */
    public function testPathStartingWithTwoSlashesAndNoAuthorityIsInvalid()
    {
        // URI "//foo" would be interpreted as network reference and thus change the original path to the host
        $this->createDefaultUri()->withPath('//foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A relative URI must not have a path beginning with a segment containing a colon
     */
    public function testRelativeUriWithPathBeginngWithColonSegmentIsInvalid()
    {
        $this->createDefaultUri()->withPath('mailto:foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRelativeUriWithPathHavingColonSegment()
    {
        $uri = (new Uri('urn:/mailto:foo'))->withScheme('');

        self::assertSame('/mailto:foo', $uri->getPath());

        (new Uri('urn:mailto:foo'))->withScheme('');
    }

    public function testDefaultReturnValuesOfGetters()
    {
        $uri = new Uri();

        self::assertSame('', $uri->getScheme());
        self::assertSame('', $uri->getAuthority());
        self::assertSame('', $uri->getUserInfo());
        self::assertSame('', $uri->getHost());
        self::assertNull($uri->getPort());
        self::assertSame('', $uri->getPath());
        self::assertSame('', $uri->getQuery());
        self::assertSame('', $uri->getFragment());
    }

    public function testImmutability()
    {
        $uri = new Uri();

        self::assertNotSame($uri, $uri->withScheme('https'));
        self::assertNotSame($uri, $uri->withUserInfo('user', 'pass'));
        self::assertNotSame($uri, $uri->withHost('example.com'));
        self::assertNotSame($uri, $uri->withPort(8080));
        self::assertNotSame($uri, $uri->withPath('/path/123'));
        self::assertNotSame($uri, $uri->withQuery('q=abc'));
        self::assertNotSame($uri, $uri->withFragment('test'));
    }

    public function testExtendingClassesInstantiates()
    {
        // The non-standard port triggers a cascade of private methods which
        // should not use late static binding to access private static members.
        // If they do, this will fatal.
        self::assertInstanceOf(
            ExtendedUriTest::class,
            new ExtendedUriTest('http://h:9/')
        );
    }

    /**
     * As Per PSR7 UriInterface the host MUST be lowercased.
     */
    public function testHostnameMustBeLowerCasedAsPerPsr7Interface()
    {
        $uri = new Uri('http://WwW.ExAmPlE.CoM');

        self::assertEquals('www.example.com', $uri->getHost());
    }

    /**
     * As Per PSR7 UriInterface the scheme MUST be lowercased.
     */
    public function testSchemeMustBeLowerCasedAsPerPsr7Interface()
    {
        $uri = new Uri('hTtp://www.example.com');

        self::assertEquals('http', $uri->getScheme());
    }

    /**
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.3.
     *
     * @dataProvider pathProvider
     *
     * @param mixed $url
     * @param mixed $path
     */
    public function testPathNormalizationPerPsr7Interface($url, $path)
    {
        self::assertEquals($path, (new Uri($url))->getPath());
    }

    public function pathProvider()
    {
        return [
            ['http://example.com/%a1/psr7/rocks', '/%A1/psr7/rocks'],
            ['http://example.com/%7Epsr7/rocks', '/~psr7/rocks'],
        ];
    }

    /**
     * @dataProvider queryProvider
     *
     * The value returned MUST be percent-encoded, but MUST NOT double-encode
     * any characters. To determine what characters to encode, please refer to
     * RFC 3986, Sections 2 and 3.4.
     *
     * @param mixed $query
     * @param mixed $expected
     */
    public function testGetQuery($query, $expected)
    {
        $uri = (new Uri())->withQuery($query);

        self::assertEquals($expected, $uri->getQuery(), 'Query must be normalized according to RFC3986');
    }

    public function queryProvider()
    {
        return [
            'normalized query' => ['foo.bar=%7evalue', 'foo.bar=~value'],
            'empty query'      => ['', ''],
            'same param query' => ['foo.bar=1&foo.bar=1', 'foo.bar=1&foo.bar=1'],
        ];
    }

    /**
     * This assertion MAY need clarification as it is not stated in
     * the interface if for the string representation indivual
     * normalization MUST be applied prior to generate the string
     * with the __toString() method.
     */
    public function testUrlStandardNormalization()
    {
        $uri = new Uri('hTtp://WwW.ExAmPlE.CoM/%a1/%7Epsr7/rocks');

        self::assertEquals('http://www.example.com/%A1/~psr7/rocks', (string) $uri);
    }

    /**
     * Authority delimiter addition should follow PSR-7 interface
     * in the following examples.
     *
     * Some of these example return invalid Url
     *
     * @dataProvider authorityProvider
     *
     * @param mixed $url
     */
    public function testAuthorityDelimiterPresence($url)
    {
        self::assertEquals($url, (string) new Uri($url));
    }

    public function authorityProvider()
    {
        return [
            ['//www.example.com'],
            ['http:www.example.com'],
            ['http:/www.example.com'],
        ];
    }

    /**
     * As Per PSR7 UriInterface the null value remove the port info
     * no InvalidArgumentException should be thrown.
     */
    public function testWithPortWithNullValue()
    {
        $uri = new Uri('http://www.example.com:81');

        self::assertNull($uri->withPort(null)->getPort());
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
     *
     * @param mixed $scheme
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $host
     * @param mixed $port
     * @param mixed $path
     * @param mixed $query
     * @param mixed $fragment
     * @param mixed $expected
     */
    public function testToString($scheme, $user, $pass, $host, $port, $path, $query, $fragment, $expected)
    {
        $uri = $this->createDefaultUri()
                ->withHost($host)
                ->withScheme($scheme)
                ->withUserInfo($user, $pass)
                ->withPort($port)
                ->withPath($path)
                ->withQuery($query)
                ->withFragment($fragment);

        self::assertEquals($expected, (string) $uri, 'URI string must be normalized according to RFC3986 rules');
    }

    public function stringProvider()
    {
        return [
            'URL normalized' => [
                'scheme'   => 'HtTps',
                'user'     => 'iGoR',
                'pass'     => 'rAsMuZeN',
                'host'     => 'MaStEr.eXaMpLe.CoM',
                'port'     => 443,
                'path'     => '/%7ejohndoe/%a1/index.php',
                'query'    => 'foo.bar=%7evalue',
                'fragment' => 'fragment',
                'uri'      => 'https://iGoR:rAsMuZeN@master.example.com/~johndoe/%A1/index.php?foo.bar=~value#fragment',
            ],
            'URL without scheme' => [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => 'www.example.com',
                'port'     => 443,
                'path'     => '/foo/bar',
                'query'    => 'param=value',
                'fragment' => 'fragment',
                'uri'      => '//www.example.com:443/foo/bar?param=value#fragment',
            ],
        ];
    }

    /**
     * @dataProvider invalidStringProvider
     * @expectedException \InvalidArgumentException
     *
     * @param mixed $scheme
     * @param mixed $user
     * @param mixed $pass
     * @param mixed $host
     * @param mixed $port
     * @param mixed $path
     * @param mixed $query
     * @param mixed $fragment
     * @param mixed $expected
     */
    public function testInvalidToString($scheme, $user, $pass, $host, $port, $path, $query, $fragment, $expected)
    {
        $uri = $this->createDefaultUri()
                ->withHost($host)
                ->withScheme($scheme)
                ->withUserInfo($user, $pass)
                ->withPort($port)
                ->withPath($path)
                ->withQuery($query)
                ->withFragment($fragment);

        self::assertEquals(
            $expected,
            (string) $uri,
            'URI components cannot be recomposed to a valid URI reference which may even depend on the current URI scheme.'
        );
    }

    public function invalidStringProvider()
    {
        return [
            'URL without rootless path' => [
                'scheme'   => 'http',
                'user'     => '',
                'pass'     => '',
                'host'     => 'www.example.com',
                'port'     => null,
                'path'     => 'foo/bar',
                'query'    => '',
                'fragment' => '',
                'uri'      => 'http://www.example.com/foo/bar',
            ],
            'URL without authority and scheme' => [
                'scheme'   => '',
                'user'     => '',
                'pass'     => '',
                'host'     => '',
                'port'     => null,
                'path'     => '//foo/bar',
                'query'    => '',
                'fragment' => '',
                'uri'      => '/foo/bar',
            ],
        ];
    }

    /**
     * @dataProvider hostProvider
     *
     * Host MUST be normalized to lowercase if present
     *
     * @param mixed $host
     * @param mixed $expected
     */
    public function testGetHost($host, $expected)
    {
        $uri = $this->createDefaultUri()->withHost($host);

        self::assertEquals($expected, $uri->getHost(), 'Host must be normalized according to RFC3986');
    }

    public function hostProvider()
    {
        return [
            'normalized host' => ['MaStEr.eXaMpLe.CoM', 'master.example.com'],
            'simple host'     => ['www.example.com', 'www.example.com'],
            'IDN hostname'    => ['مثال.إختبار', 'مثال.إختبار'],
            'IPv6 Host'       => ['[::1]', '[::1]'],
        ];
    }

    /**
     * @dataProvider withHostFailedProvider
     * @expectedException \InvalidArgumentException
     *
     * @param mixed $host
     */
    public function testWithHostFailed($host)
    {
        $this->createDefaultUri()->withHost($host);
    }

    public function withHostFailedProvider()
    {
        return [
            'dot in front'                         => ['.example.com'],
            'hyphen suffix'                        => ['host.com-'],
            'multiple dot'                         => ['.......'],
            'one dot'                              => ['.'],
            'empty label'                          => ['tot.    .coucou.com'],
            'space in the label'                   => ['re view'],
            'underscore in label'                  => ['_bad.host.com'],
            'label too long'                       => [implode('', array_fill(0, 12, 'banana')) . '.secure.example.com'],
            'too many labels'                      => [implode('.', array_fill(0, 128, 'a'))],
            'Invalid IPv4 format'                  => ['[127.0.0.1]'],
            'Invalid IPv6 format'                  => ['[[::1]]'],
            'Invalid IPv6 format 2'                => ['[::1'],
            'space character in starting label'    => ['example. com'],
            'invalid character in host label'      => ["examp\0le.com"],
            'invalid IP with scope'                => ['[127.2.0.1%253]'],
            'invalid scope IPv6'                   => ['ab23::1234%251'],
            'invalid scope ID'                     => ['fe80::1234%25?@'],
            'invalid scope ID with utf8 character' => ['fe80::1234%25€'],
        ];
    }

    /**
     * @dataProvider utf8PathsDataProvider
     *
     * @param mixed $url
     * @param mixed $result
     */
    public function testUtf8Path($url, $result)
    {
        $uri = new Uri($url);

        self::assertEquals($result, $uri->getPath());
    }

    public function utf8PathsDataProvider()
    {
        return [
            ['http://example.com/тестовый_путь/', '/тестовый_путь/'],
            ['http://example.com/ουτοπία/', '/ουτοπία/'],
        ];
    }
}
