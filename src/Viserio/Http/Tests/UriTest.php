<?php
namespace Viserio\Http\Tests;

use Psr\Http\Message\UriInterface;
use Viserio\Http\Tests\Constraint\Immutable;
use Viserio\Http\Tests\Fixture\ExtendedUriTest;
use Viserio\Http\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    const RFC3986_BASE = 'http://a/b/c/d;p?q';

    public function testParsesProvidedUri()
    {
        $uri = new Uri('https://user:pass@example.com:8080/path/123?q=abc#test');

        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/123', $uri->getPath());
        $this->assertSame('q=abc', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string) $uri);
    }

    public function testCanTransformAndRetrievePartsIndividually()
    {
        $uri = (new Uri())
            ->withScheme('https')
            ->withUserInfo('user', 'pass')
            ->withHost('example.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('q=abc')
            ->withFragment('test');
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/123', $uri->getPath());
        $this->assertSame('q=abc', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string) $uri);
    }

    /**
     * @dataProvider getValidUris
     */
    public function testValidUrisStayValid($input)
    {
        $uri = new Uri($input);
        $this->assertSame($input, (string) $uri);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The source URI string appears to be malformed
     * @dataProvider getInvalidUris
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
        (new Uri())->withPort(100000);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid port: 0. Must be between 1 and 65535
     */
    public function testWithPortCannotBeZero()
    {
        (new Uri())->withPort(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The source URI string appears to be malformed
     */
    public function testParseUriPortCannotBeZero()
    {
        new Uri('//example.com:0');
    }

    /**
     * @expectedException \TypeError
     */
    public function testSchemeMustHaveCorrectType()
    {
        (new Uri())->withScheme([]);
    }

    /**
     * @expectedException \TypeError
     */
    public function testHostMustHaveCorrectType()
    {
        (new Uri())->withHost([]);
    }

    /**
     * @expectedException \TypeError
     */
    public function testPathMustHaveCorrectType()
    {
        (new Uri())->withPath([]);
    }

    /**
     * @expectedException \TypeError
     */
    public function testQueryMustHaveCorrectType()
    {
        (new Uri())->withQuery([]);
    }

    /**
     * @expectedException \TypeError
     */
    public function testFragmentMustHaveCorrectType()
    {
        (new Uri())->withFragment([]);
    }

    public function testCantParseFalseyUriParts()
    {
        $uri = new Uri('0://0:0@0/0?0#0');
        $this->assertSame('', $uri->getScheme());
        $this->assertSame('0', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('0', $uri->getHost());
        $this->assertSame('//0:0@0/0', $uri->getPath());
        $this->assertSame('0', $uri->getQuery());
        $this->assertSame('0', $uri->getFragment());
        $this->assertSame('//0//0:0@0/0?0#0', (string) $uri);
    }

    public function testCanConstructFalseyUriParts()
    {
        $uri = (new Uri())
            ->withScheme('0')
            ->withUserInfo('0', '0')
            ->withHost('0')
            ->withPath('/0')
            ->withQuery('0')
            ->withFragment('0');
        $this->assertSame('0', $uri->getScheme());
        $this->assertSame('0:0@0', $uri->getAuthority());
        $this->assertSame('0:0', $uri->getUserInfo());
        $this->assertSame('0', $uri->getHost());
        $this->assertSame('/0', $uri->getPath());
        $this->assertSame('0', $uri->getQuery());
        $this->assertSame('0', $uri->getFragment());
        $this->assertSame('0://0:0@0/0?0#0', (string) $uri);
    }

    public function testSchemeIsNormalizedToLowercase()
    {
        $uri = new Uri('HTTP://example.com');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('http://example.com', (string) $uri);
        $uri = (new Uri('//example.com'))->withScheme('HTTP');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('http://example.com', (string) $uri);
    }

    public function testHostIsNormalizedToLowercase()
    {
        $uri = new Uri('//eXaMpLe.CoM');
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('//example.com', (string) $uri);
        $uri = (new Uri())->withHost('eXaMpLe.CoM');
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame('//example.com', (string) $uri);
    }

    public function testPortIsNullIfStandardPortForScheme()
    {
        // HTTPS standard port
        $uri = new Uri('https://example.com:443');
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());

        $uri = (new Uri('https://example.com'))->withPort(443);
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());

        // HTTP standard port
        $uri = new Uri('http://example.com:80');
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());

        $uri = (new Uri('http://example.com'))->withPort(80);
        $this->assertNull($uri->getPort());
        $this->assertSame('example.com', $uri->getAuthority());
    }

    public function testPortIsReturnedIfSchemeUnknown()
    {
        $uri = (new Uri('//example.com'))->withPort(80);
        $this->assertSame(80, $uri->getPort());
        $this->assertSame('example.com:80', $uri->getAuthority());
    }

    public function testStandardPortIsNullIfSchemeChanges()
    {
        $uri = new Uri('http://example.com:443');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame(443, $uri->getPort());
        $uri = $uri->withScheme('https');
        $this->assertNull($uri->getPort());
    }

    public function testPortPassedAsStringIsCastedToInt()
    {
        $uri = (new Uri('//example.com'))->withPort('8080');
        $this->assertSame(8080, $uri->getPort(), 'Port is returned as integer');
        $this->assertSame('example.com:8080', $uri->getAuthority());
    }

    public function testPortCanBeRemoved()
    {
        $uri = (new Uri('http://example.com:8080'))->withPort(null);
        $this->assertNull($uri->getPort());
        $this->assertSame('http://example.com', (string) $uri);
    }

    /**
     * In RFC 8986 the host is optional and the authority can only
     * consist of the user info and port.
     */
    public function testAuthorityWithUserInfoOrPortButWithoutHost()
    {
        $uri = (new Uri())->withUserInfo('user', 'pass');
        $this->assertSame('user:pass', $uri->getUserInfo());
        $this->assertSame('user:pass@', $uri->getAuthority());

        $uri = $uri->withPort(8080);
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('user:pass@:8080', $uri->getAuthority());
        $this->assertSame('//user:pass@:8080', (string) $uri);

        $uri = $uri->withUserInfo('');
        $this->assertSame(':8080', $uri->getAuthority());
    }

    public function testHostInHttpUriDefaultsToLocalhost()
    {
        $uri = (new Uri())->withScheme('http');

        $this->assertSame('localhost', $uri->getHost());
        $this->assertSame('localhost', $uri->getAuthority());
        $this->assertSame('http://localhost', (string) $uri);
    }

    public function testHostInHttpsUriDefaultsToLocalhost()
    {
        $uri = (new Uri())->withScheme('https');

        $this->assertSame('localhost', $uri->getHost());
        $this->assertSame('localhost', $uri->getAuthority());
        $this->assertSame('https://localhost', (string) $uri);
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
     */
    public function testUriComponentsGetEncodedProperly($input, $path, $query, $fragment, $output)
    {
        $uri = new Uri($input);
        $this->assertSame($path, $uri->getPath());
        $this->assertSame($query, $uri->getQuery());
        $this->assertSame($fragment, $uri->getFragment());
        $this->assertSame($output, (string) $uri);
    }

    public function testWithPathEncodesProperly()
    {
        $uri = (new Uri())->withPath('/baz?#€/b%61r');
        // Query and fragment delimiters and multibyte chars are encoded.
        $this->assertSame('/baz%3F%23%E2%82%AC/b%61r', $uri->getPath());
        $this->assertSame('/baz%3F%23%E2%82%AC/b%61r', (string) $uri);
    }

    public function testWithQueryEncodesProperly()
    {
        $uri = (new Uri())->withQuery('?=#&€=/&b%61r');
        // A query starting with a "?" is valid and must not be magically removed. Otherwise it would be impossible to
        // construct such an URI. Also the "?" and "/" does not need to be encoded in the query.
        $this->assertSame('?=%23&%E2%82%AC=/&b%61r', $uri->getQuery());
        $this->assertSame('??=%23&%E2%82%AC=/&b%61r', (string) $uri);
    }

    public function testWithFragmentEncodesProperly()
    {
        $uri = (new Uri())->withFragment('#€?/b%61r');
        // A fragment starting with a "#" is valid and must not be magically removed. Otherwise it would be impossible to
        // construct such an URI. Also the "?" and "/" does not need to be encoded in the fragment.
        $this->assertSame('%23%E2%82%AC?/b%61r', $uri->getFragment());
        $this->assertSame('#%23%E2%82%AC?/b%61r', (string) $uri);
    }

    public function testAllowsForRelativeUri()
    {
        $uri = (new Uri())->withPath('foo');
        $this->assertSame('foo', $uri->getPath());
        $this->assertSame('foo', (string) $uri);
    }

    public function testPathStartingWithTwoSlashes()
    {
        $uri = new Uri('http://example.org//path-not-host.com');
        $this->assertSame('//path-not-host.com', $uri->getPath());

        $uri = $uri->withScheme('');
        $this->assertSame('//example.org//path-not-host.com', (string) $uri); // This is still valid
        $this->setExpectedException('\InvalidArgumentException');
        $uri->withHost(''); // Now it becomes invalid
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The path of a URI with an authority must start with a slash "/" or be empty
     */
    public function testRelativePathAndAuhorityIsInvalid()
    {
        // concatenating a relative path with a host doesn't work: "//example.comfoo" would be wrong
        (new Uri)->withPath('foo')->withHost('example.com');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The path of a URI without an authority must not start with two slashes "//"
     */
    public function testPathStartingWithTwoSlashesAndNoAuthorityIsInvalid()
    {
        // URI "//foo" would be interpreted as network reference and thus change the original path to the host
        (new Uri)->withPath('//foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A relative URI must not have a path beginning with a segment containing a colon
     */
    public function testRelativeUriWithPathBeginngWithColonSegmentIsInvalid()
    {
        (new Uri)->withPath('mailto:foo');
    }

    public function testRelativeUriWithPathHavingColonSegment()
    {
        $uri = (new Uri('urn:/mailto:foo'))->withScheme('');
        $this->assertSame('/mailto:foo', $uri->getPath());

        $this->setExpectedException('\InvalidArgumentException');
        (new Uri('urn:mailto:foo'))->withScheme('');
    }

    public function testDefaultReturnValuesOfGetters()
    {
        $uri = new Uri();
        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', $uri->getFragment());
    }

    public function testImmutability()
    {
        $uri = new Uri();
        $this->assertNotSame($uri, $uri->withScheme('https'));
        $this->assertNotSame($uri, $uri->withUserInfo('user', 'pass'));
        $this->assertNotSame($uri, $uri->withHost('example.com'));
        $this->assertNotSame($uri, $uri->withPort(8080));
        $this->assertNotSame($uri, $uri->withPath('/path/123'));
        $this->assertNotSame($uri, $uri->withQuery('q=abc'));
        $this->assertNotSame($uri, $uri->withFragment('test'));
    }

    public function testExtendingClassesInstantiates()
    {
        // The non-standard port triggers a cascade of private methods which
        // should not use late static binding to access private static members.
        // If they do, this will fatal.
        $this->assertInstanceOf(
            '\Viserio\Http\Tests\Fixture\ExtendedUriTest',
            new ExtendedUriTest('http://h:9/')
        );
    }

    public function testProperlyTrimsLeadingSlashesToPreventXSS()
    {
        $url = 'http://example.org//anolilab.de';
        $uri = new Uri($url);
        $this->assertEquals('http://example.org/anolilab.de', (string) $uri);
    }

    /**
     * As Per PSR7 UriInterface the host MUST be lowercased
     *
     * @group uriinterface
     */
    public function testHostnameMustBeLowerCasedAsPerPsr7Interface()
    {
        $url = 'http://WwW.ExAmPlE.CoM';
        $uri = new Uri($url);
        $this->assertEquals('www.example.com', $uri->getHost());
    }

    /**
     * As Per PSR7 UriInterface the scheme MUST be lowercased
     *
     * @group uriinterface
     */
    public function testSchemeMustBeLowerCasedAsPerPsr7Interface()
    {
        $url = 'hTtp://www.example.com';
        $uri = new Uri($url);
        $this->assertEquals('http', $uri->getScheme());
    }

    /**
     * As Per PSR7 UriInterface the path MUST be encoded following
     * RFC3986 rules does it means that :
     * - the encoding characters must be uppercased or not ?
     * - the "~" character must not be encoded ?
     *
     * @group uriinterface
     * @dataProvider pathProvider
     */
    public function testPathNormalizationPerPsr7Interface($url, $path)
    {
        $this->assertEquals($path, (new Uri($url))->getPath());
    }

    public function pathProvider()
    {
        return [
            ['http://example.com/%a1/psr7/rocks', '/%A1/psr7/rocks'],
            ['http://example.com/%7Epsr7/rocks', '/~psr7/rocks'],
        ];
    }

    /**
     * This assertion MAY need clarification as it is not stated in
     * the interface if for the string representation indivual
     * normalization MUST be applied prior to generate the string
     * with the __toString() method
     *
     * @group uriinterface
     */
    public function testUrlStandardNormalization()
    {
        $url = 'hTtp://WwW.ExAmPlE.CoM/%a1/%7Epsr7/rocks';
        $uri = new Uri($url);
        $this->assertEquals('http://www.example.com/%A1/~psr7/rocks', (string) $uri);
    }

    /**
     * Authority delimiter addition should follow PSR-7 interface
     * in the following examples.
     *
     * Some of these example return invalid Url
     *
     * @group uriinterface
     * @dataProvider authorityProvider
     */
    public function testAuthorityDelimiterPresence($url)
    {
        $this->assertEquals($url, (string) new Uri($url));
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
     * no InvalidArgumentException should be thrown
     *
     * @group uriinterface
     */
    public function testWithPortWithNullValue()
    {
        $url = 'http://www.example.com:81';
        $uri = new Uri($url);
        $this->assertNull($uri->withPort(null)->getPort());
    }

    /**
     * @dataProvider utf8PathsDataProvider
     */
    public function testUtf8Path($url, $result)
    {
        $uri = new Uri($url);

        $this->assertEquals($result, $uri->getPath());
    }

    public function utf8PathsDataProvider()
    {
        return [
            ['http://example.com/тестовый_путь/', '/тестовый_путь/'],
            ['http://example.com/ουτοπία/', '/ουτοπία/']
        ];
    }
}
