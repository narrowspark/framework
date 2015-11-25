<?php
namespace Viserio\Http\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Mockery as Mock;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Viserio\Http\Request;

/**
 * HttpRequestTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class HttpRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * [tearDown description].
     *
     * @return [type] [description]
     */
    public function tearDown()
    {
        Mock::close();
    }

    /**
     * [testInstanceMethod description].
     *
     * @return [type] [description]
     */
    public function testInstanceMethod()
    {
        $request = Request::create('', 'GET');
        $this->assertSame($request, $request->instance());
    }

    /**
     * [testRootMethod description].
     *
     * @return [type] [description]
     */
    public function testRootMethod()
    {
        $request = Request::create('http://example.com/foo/bar/script.php?test');
        $this->assertEquals('http://example.com', $request->root());
    }

    /**
     * [testPathMethod description].
     *
     * @return [type] [description]
     */
    public function testPathMethod()
    {
        $request = Request::create('', 'GET');
        $this->assertEquals('/', $request->path());
        $request = Request::create('/foo/bar', 'GET');
        $this->assertEquals('foo/bar', $request->path());
    }

    /**
     * [testDecodedPathMethod description].
     *
     * @return [type] [description]
     */
    public function testDecodedPathMethod()
    {
        $request = Request::create('/foo%20bar');
        $this->assertEquals('foo bar', $request->decodedPath());
    }

    /**
     * @dataProvider segmentProvider
     */
    public function testSegmentMethod($path, $segment, $expected)
    {
        $request = Request::create($path, 'GET');
        $this->assertEquals($expected, $request->segment($segment, 'default'));
    }

    /**
     * [segmentProvider description].
     *
     * @return [type] [description]
     */
    public function segmentProvider()
    {
        return [
            ['', 1, 'default'],
            ['foo/bar//baz', '1', 'foo'],
            ['foo/bar//baz', '2', 'bar'],
            ['foo/bar//baz', '3', 'baz'],
        ];
    }

    /**
     * @dataProvider segmentsProvider
     */
    public function testSegmentsMethod($path, $expected)
    {
        $request = Request::create($path, 'GET');
        $this->assertEquals($expected, $request->segments());
        $request = Request::create('foo/bar', 'GET');
        $this->assertEquals(['foo', 'bar'], $request->segments());
    }

    /**
     * [segmentsProvider description].
     *
     * @return [type] [description]
     */
    public function segmentsProvider()
    {
        return [
            ['', []],
            ['foo/bar', ['foo', 'bar']],
            ['foo/bar//baz', ['foo', 'bar', 'baz']],
            ['foo/0/bar', ['foo', '0', 'bar']],
        ];
    }

    /**
     * [testUrlMethod description].
     *
     * @return [type] [description]
     */
    public function testUrlMethod()
    {
        $request = Request::create('http://foo.com/foo/bar?name=Narrowspark', 'GET');
        $this->assertEquals('http://foo.com/foo/bar', $request->url());
        $request = Request::create('http://foo.com/foo/bar/?', 'GET');
        $this->assertEquals('http://foo.com/foo/bar', $request->url());
    }

    /**
     * [testFullUrlMethod description].
     *
     * @return [type] [description]
     */
    public function testFullUrlMethod()
    {
        $request = Request::create('http://foo.com/foo/bar?name=Narrowspark', 'GET');
        $this->assertEquals('http://foo.com/foo/bar?name=Narrowspark', $request->fullUrl());
        $request = Request::create('https://foo.com', 'GET');
        $this->assertEquals('https://foo.com', $request->fullUrl());
    }

    /**
     * [testIsMethod description].
     *
     * @return [type] [description]
     */
    public function testIsMethod()
    {
        $request = Request::create('/foo/bar', 'GET');
        $this->assertTrue($request->is('foo*'));
        $this->assertFalse($request->is('bar*'));
        $this->assertTrue($request->is('*bar*'));
        $this->assertTrue($request->is('bar*', 'foo*', 'baz'));

        $request = Request::create('/', 'GET');
        $this->assertTrue($request->is('/'));
    }

    /**
     * [testAjaxMethod description].
     *
     * @return [type] [description]
     */
    public function testAjaxMethod()
    {
        $request = Request::create('/', 'GET');
        $this->assertFalse($request->ajax());

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'], '{}');
        $this->assertTrue($request->ajax());

        $request = Request::create('/', 'POST');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $this->assertTrue($request->ajax());

        $request->headers->set('X-Requested-With', '');
        $this->assertFalse($request->ajax());
    }

    public function testFormatReturnsAcceptableFormat()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $this->assertEquals('json', $request->format());
        $this->assertTrue($request->wantsJson());

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/atom+xml']);
        $this->assertEquals('atom', $request->format());
        $this->assertFalse($request->wantsJson());

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'is/not/known']);
        $this->assertEquals('html', $request->format());
        $this->assertEquals('foo', $request->format('foo'));
    }

    public function testFormatReturnsAcceptsJson()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $this->assertEquals('json', $request->format());
        $this->assertTrue($request->accepts('application/json'));
        $this->assertTrue($request->accepts('application/baz+json'));
        $this->assertTrue($request->acceptsJson());
        $this->assertFalse($request->acceptsHtml());

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/foo+json']);
        $this->assertTrue($request->accepts('application/foo+json'));
        $this->assertFalse($request->accepts('application/bar+json'));
        $this->assertFalse($request->accepts('application/json'));

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/*']);
        $this->assertTrue($request->accepts('application/xml'));
        $this->assertTrue($request->accepts('application/json'));
    }

    public function testFormatReturnsAcceptsHtml()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'text/html']);
        $this->assertEquals('html', $request->format());
        $this->assertTrue($request->accepts('text/html'));
        $this->assertTrue($request->acceptsHtml());
        $this->assertFalse($request->acceptsJson());

        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'text/*']);
        $this->assertTrue($request->accepts('text/html'));
        $this->assertTrue($request->accepts('text/plain'));
    }

    public function testFormatReturnsAcceptsAll()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => '*/*']);
        $this->assertEquals('html', $request->format());
        $this->assertTrue($request->accepts('text/html'));
        $this->assertTrue($request->accepts('foo/bar'));
        $this->assertTrue($request->accepts('application/baz+xml'));
        $this->assertTrue($request->acceptsHtml());
        $this->assertTrue($request->acceptsJson());
    }

    public function testFormatReturnsAcceptsMultiple()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json,text/*']);
        $this->assertTrue($request->accepts(['text/html', 'application/json']));
        $this->assertTrue($request->accepts('text/html'));
        $this->assertTrue($request->accepts('text/foo'));
        $this->assertTrue($request->accepts('application/json'));
        $this->assertTrue($request->accepts('application/baz+json'));
    }

    public function testPjaxMethod()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_X_PJAX' => 'true'], '{}');
        $this->assertTrue($request->isPjax());

        $request->headers->set('X-PJAX', 'false');
        $this->assertTrue($request->isPjax());

        $request->headers->set('X-PJAX', null);
        $this->assertFalse($request->isPjax());

        $request->headers->set('X-PJAX', '');
        $this->assertFalse($request->isPjax());
    }

    /**
     * [testSecureMethod description].
     *
     * @return [type] [description]
     */
    public function testSecureMethod()
    {
        $request = Request::create('http://example.com', 'GET');
        $this->assertFalse($request->isSecure());

        $request = Request::create('https://example.com', 'GET');
        $this->assertTrue($request->isSecure());
    }

    /**
     * [testHasMethod description].
     *
     * @return [type] [description]
     */
    public function testHasMethod()
    {
        $request = Request::create('/', 'GET', ['name' => 'Narrowspark']);
        $this->assertTrue($request->has('name'));
        $this->assertFalse($request->has('foo'));
        $this->assertFalse($request->has('name', 'email'));
        $request = Request::create('/', 'GET', ['name' => 'Narrowspark', 'email' => 'foo']);
        $this->assertTrue($request->has('name'));
        $this->assertTrue($request->has('name', 'email'));
        //test arrays within query string
        $request = Request::create('/', 'GET', ['foo' => ['bar', 'baz']]);
        $this->assertTrue($request->has('foo'));
    }

    /**
     * [testInputMethod description].
     *
     * @return [type] [description]
     */
    public function testInputMethod()
    {
        $request = Request::create('/', 'GET', ['name' => 'Narrowspark']);
        $this->assertEquals('Narrowspark', $request->input('name'));
        $this->assertEquals('Dani', $request->input('foo', 'Dani'));
    }

    /**
     * [testOnlyMethod description].
     *
     * @return [type] [description]
     */
    public function testOnlyMethod()
    {
        $request = Request::create('/', 'GET', ['name' => 'Narrowspark', 'age' => 25]);
        $this->assertEquals(['age' => 25], $request->only('age'));
        $this->assertEquals(['name' => 'Narrowspark', 'age' => 25], $request->only('name', 'age'));
        $request = Request::create('/', 'GET', ['developer' => ['name' => 'Narrowspark', 'age' => 25]]);
        $this->assertEquals(['developer' => ['age' => 25]], $request->only('developer.age'));
        $this->assertEquals(['developer' => ['name' => 'Narrowspark'], 'test' => null], $request->only('developer.name', 'test'));
    }

    /**
     * [testExceptMethod description].
     *
     * @return [type] [description]
     */
    public function testExceptMethod()
    {
        $request = Request::create('/', 'GET', ['name' => 'Narrowspark', 'age' => 25]);
        $this->assertEquals(['name' => 'Narrowspark'], $request->except('age'));
        $this->assertEquals([], $request->except('age', 'name'));
    }

    /**
     * [testQueryMethod description].
     *
     * @return [type] [description]
     */
    public function testQueryMethod()
    {
        $request = Request::create('/', 'GET', ['name' => 'Narrowspark']);
        $this->assertEquals('Narrowspark', $request->query('name'));
        $this->assertEquals('Dani', $request->query('foo', 'Dani'));
        $all = $request->query(null);
        $this->assertEquals('Narrowspark', $all['name']);
    }

    /**
     * [testCookieMethod description].
     *
     * @return [type] [description]
     */
    public function testCookieMethod()
    {
        $request = Request::create('/', 'GET', [], ['name' => 'Narrowspark']);
        $this->assertEquals('Narrowspark', $request->getCookie('name'));
        $this->assertEquals('Dani', $request->getCookie('foo', 'Dani'));
        $all = $request->getCookie(null);
        $this->assertEquals('Narrowspark', $all['name']);
    }

    /**
     * [testHasCookieMethod description].
     *
     * @return [type] [description]
     */
    public function testHasCookieMethod()
    {
        $request = Request::create('/', 'GET', [], ['foo' => 'bar']);
        $this->assertTrue($request->hasCookie('foo'));
        $this->assertFalse($request->hasCookie('qu'));
    }

    /**
     * [testFileMethod description].
     *
     * @return [type] [description]
     */
    public function testFileMethod()
    {
        $files = [
            'foo' => [
                'size' => 500,
                'name' => 'foo.jpg',
                'tmp_name' => __FILE__,
                'type' => 'blah',
                'error' => null,
            ],
        ];
        $request = Request::create('/', 'GET', [], [], $files);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\UploadedFile', $request->files('foo'));
    }

    /**
     * [testHasFileMethod description].
     *
     * @return [type] [description]
     */
    public function testHasFileMethod()
    {
        $request = Request::create('/', 'GET', [], [], []);
        $this->assertFalse($request->hasfiles('foo'));
        $files = [
            'foo' => [
                'size' => 500,
                'name' => 'foo.jpg',
                'tmp_name' => __FILE__,
                'type' => 'blah',
                'error' => null,
            ],
        ];
        $request = Request::create('/', 'GET', [], [], $files);
        $this->assertTrue($request->hasfiles('foo'));
    }

    /**
     * [testServerMethod description].
     *
     * @return [type] [description]
     */
    public function testServerMethod()
    {
        $request = Request::create('/', 'GET', [], [], [], ['foo' => 'bar']);
        $this->assertEquals('bar', $request->server('foo'));
        $this->assertEquals('bar', $request->server('foo.doesnt.exist', 'bar'));
        $all = $request->server(null);
        $this->assertEquals('bar', $all['foo']);
    }

    /**
     * [testMergeMethod description].
     *
     * @return [type] [description]
     */
    public function testMergeMethod()
    {
        $request = Request::create('/', 'GET', ['name' => 'Narrowspark']);
        $merge = ['buddy' => 'Dayle'];
        $request->merge($merge);
        $this->assertEquals('Narrowspark', $request->input('name'));
        $this->assertEquals('Dayle', $request->input('buddy'));
    }

    /**
     * [testReplaceMethod description].
     *
     * @return [type] [description]
     */
    public function testReplaceMethod()
    {
        $request = Request::create('/', 'GET', ['name' => 'Narrowspark']);
        $replace = ['buddy' => 'Dayle'];
        $request->replace($replace);
        $this->assertNull($request->input('name'));
        $this->assertEquals('Dayle', $request->input('buddy'));
    }

    /**
     * [testHeaderMethod description].
     *
     * @return [type] [description]
     */
    public function testHeaderMethod()
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_DO_THIS' => 'foo']);
        $this->assertEquals('foo', $request->headers('do-this'));
        $all = $request->headers(null);
        $this->assertEquals('foo', $all['do-this'][0]);
    }

    /**
     * [testJSONMethod description].
     *
     * @return [type] [description]
     */
    public function testJSONMethod()
    {
        $payload = ['name' => 'Narrowspark'];
        $request = Request::create('/', 'GET', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertEquals('Narrowspark', $request->json('name'));
        $this->assertEquals('Narrowspark', $request->input('name'));
        $data = $request->json()->all();
        $this->assertEquals($payload, $data);
    }

    public function testHasRegexMethod()
    {
        $request = Request::create('/', 'GET', ['name' => 'Viserio', 'foo' => 'bar']);
        $this->assertFalse($request->hasRegex('/^utm_/'));
        $this->assertTrue($request->hasRegex('/name/'));
        $this->assertTrue($request->hasRegex('/^foo$/'));

        $request = Request::create('/', 'GET', ['name' => '', 'utm_source' => 'narrowspark', 'foo' => null]);
        $this->assertTrue($request->hasRegex('/^utm_/'));
        $this->assertFalse($request->hasRegex('/foo/'));
        $this->assertFalse($request->hasRegex('/name/'));
    }

    /**
     * [testJSONEmulatingPHPBuiltInServer description].
     *
     * @return [type] [description]
     */
    public function testJSONEmulatingPHPBuiltInServer()
    {
        $payload = ['name' => 'Narrowspark'];
        $content = json_encode($payload);
        // The built in PHP 5.4 webserver incorrectly provides HTTP_CONTENT_TYPE and HTTP_CONTENT_LENGTH,
        // rather than CONTENT_TYPE and CONTENT_LENGTH
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_CONTENT_TYPE' => 'application/json', 'HTTP_CONTENT_LENGTH' => strlen($content)], $content);
        $this->assertTrue($request->isJson());
        $data = $request->json()->all();
        $this->assertEquals($payload, $data);
        $data = $request->all();
        $this->assertEquals($payload, $data);
    }

    /**
     * [testAllInputReturnsInputAndFiles description].
     *
     * @return [type] [description]
     */
    public function testAllInputReturnsInputAndFiles()
    {
        $file = $this->getMock('Symfony\Component\HttpFoundation\File\UploadedFile', null, [__FILE__, 'photo.jpg']);
        $request = Request::create('/?boom=breeze', 'GET', ['foo' => 'bar'], [], ['baz' => $file]);
        $this->assertEquals(['foo' => 'bar', 'baz' => $file, 'boom' => 'breeze'], $request->all());
    }

    /**
     * [testAllInputReturnsNestedInputAndFiles description].
     *
     * @return [type] [description]
     */
    public function testAllInputReturnsNestedInputAndFiles()
    {
        $file = $this->getMock('Symfony\Component\HttpFoundation\File\UploadedFile', null, [__FILE__, 'photo.jpg']);
        $request = Request::create('/?boom=breeze', 'GET', ['foo' => ['bar' => 'baz']], [], ['foo' => ['photo' => $file]]);
        $this->assertEquals(['foo' => ['bar' => 'baz', 'photo' => $file], 'boom' => 'breeze'], $request->all());
    }

    /**
     * [testAllInputReturnsInputAfterReplace description].
     *
     * @return [type] [description]
     */
    public function testAllInputReturnsInputAfterReplace()
    {
        $request = Request::create('/?boom=breeze', 'GET', ['foo' => ['bar' => 'baz']]);
        $request->replace(['foo' => ['bar' => 'baz'], 'boom' => 'breeze']);
        $this->assertEquals(['foo' => ['bar' => 'baz'], 'boom' => 'breeze'], $request->all());
    }

    /**
     * [testAllInputWithNumericKeysReturnsInputAfterReplace description].
     *
     * @return [type] [description]
     */
    public function testAllInputWithNumericKeysReturnsInputAfterReplace()
    {
        $request1 = Request::create('/', 'POST', [0 => 'A', 1 => 'B', 2 => 'C']);
        $request1->replace([0 => 'A', 1 => 'B', 2 => 'C']);
        $this->assertEquals([0 => 'A', 1 => 'B', 2 => 'C'], $request1->all());
        $request2 = Request::create('/', 'POST', [1 => 'A', 2 => 'B', 3 => 'C']);
        $request2->replace([1 => 'A', 2 => 'B', 3 => 'C']);
        $this->assertEquals([1 => 'A', 2 => 'B', 3 => 'C'], $request2->all());
    }

    /**
     * [testInputWithEmptyFilename description].
     *
     * @return [type] [description]
     */
    public function testInputWithEmptyFilename()
    {
        $invalidFiles = [
            'file' => [
                'name' => null,
                'type' => null,
                'tmp_name' => null,
                'error' => 4,
                'size' => 0,
            ],
        ];

        $baseRequest = SymfonyRequest::create('/?boom=breeze', 'GET', ['foo' => ['bar' => 'baz']], [], $invalidFiles);

        $request = Request::createFromBase($baseRequest);
    }

    /**
     * [testExistsMethod description].
     *
     * @return [type] [description]
     */
    public function testExistsMethod()
    {
        $request = Request::create('/', 'GET', ['name' => 'Taylor']);
        $this->assertTrue($request->exists('name'));
        $this->assertFalse($request->exists('foo'));
        $this->assertFalse($request->exists('name', 'email'));

        $request = Request::create('/', 'GET', ['name' => 'Taylor', 'email' => 'foo']);
        $this->assertTrue($request->exists('name'));
        $this->assertTrue($request->exists('name', 'email'));

        $request = Request::create('/', 'GET', ['foo' => ['bar', 'bar']]);
        $this->assertTrue($request->exists('foo'));

        $request = Request::create('/', 'GET', ['foo' => '', 'bar' => null]);
        $this->assertTrue($request->exists('foo'));
        $this->assertTrue($request->exists('bar'));
    }

    /**
     * [testCreateFromBase description].
     *
     * @return [type] [description]
     */
    public function testCreateFromBase()
    {
        $body = [
            'foo' => 'bar',
            'baz' => ['qux'],
        ];
        $server = [
            'CONTENT_TYPE' => 'application/json',
        ];

        $base = SymfonyRequest::create('/', 'GET', [], [], [], $server, json_encode($body));

        $request = Request::createFromBase($base);

        $this->assertEquals($request->request->all(), $body);
    }
}
