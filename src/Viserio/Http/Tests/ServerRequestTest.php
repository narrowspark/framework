<?php
declare(strict_types=1);
namespace Viserio\Http\Tests;

use Viserio\Http\ServerRequest;
use Viserio\Http\UploadedFile;

class ServerRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testUploadedFiles()
    {
        $request1 = new ServerRequest('', 'GET');
        $files    = [
            'file'  => new UploadedFile('test', 123, UPLOAD_ERR_OK),
            'file2' => [
                new UploadedFile('test', 123, UPLOAD_ERR_OK),
            ],
        ];
        $request2 = $request1->withUploadedFiles($files);

        self::assertNotSame($request2, $request1);
        self::assertSame([], $request1->getUploadedFiles());
        self::assertSame($files, $request2->getUploadedFiles());
    }

    public function testServerParams()
    {
        $params  = ['name' => 'value'];
        $request = new ServerRequest('/', 'GET', [], null, '1.1', $params);

        self::assertSame($params, $request->getServerParams());
    }

    public function testCookieParams()
    {
        $request1 = new ServerRequest('/', 'GET');
        $params   = ['name' => 'value'];
        $request2 = $request1->withCookieParams($params);

        self::assertNotSame($request2, $request1);
        self::assertEmpty($request1->getCookieParams());
        self::assertSame($params, $request2->getCookieParams());
    }

    public function testQueryParams()
    {
        $request1 = new ServerRequest('/', 'GET');
        $params   = ['name' => 'value'];
        $request2 = $request1->withQueryParams($params);

        self::assertNotSame($request2, $request1);
        self::assertEmpty($request1->getQueryParams());
        self::assertSame($params, $request2->getQueryParams());
    }

    public function testParsedBody()
    {
        $request1 = new ServerRequest('/', 'GET');
        $params   = ['name' => 'value'];
        $request2 = $request1->withParsedBody($params);

        self::assertNotSame($request2, $request1);
        self::assertEmpty($request1->getParsedBody());
        self::assertSame($params, $request2->getParsedBody());
    }

    public function testAttributes()
    {
        $request1 = new ServerRequest('/', 'GET');
        $request2 = $request1->withAttribute('name', 'value');
        $request3 = $request2->withAttribute('other', 'otherValue');
        $request4 = $request3->withoutAttribute('other');
        $request5 = $request3->withoutAttribute('unknown');

        self::assertNotSame($request2, $request1);
        self::assertNotSame($request3, $request2);
        self::assertNotSame($request4, $request3);
        self::assertNotSame($request5, $request4);
        self::assertEmpty($request1->getAttributes());
        self::assertEmpty($request1->getAttribute('name'));
        self::assertEquals(
            'something',
            $request1->getAttribute('name', 'something'),
            'Should return the default value'
        );
        self::assertEquals('value', $request2->getAttribute('name'));
        self::assertEquals(['name' => 'value'], $request2->getAttributes());
        self::assertEquals(['name' => 'value', 'other' => 'otherValue'], $request3->getAttributes());
        self::assertEquals(['name' => 'value'], $request4->getAttributes());
    }

    public function testNullAttribute()
    {
        $request = (new ServerRequest('/', 'GET'))->withAttribute('name', null);

        self::assertSame(['name' => null], $request->getAttributes());
        self::assertNull($request->getAttribute('name', 'different-default'));

        $requestWithoutAttribute = $request->withoutAttribute('name');

        self::assertSame([], $requestWithoutAttribute->getAttributes());
        self::assertSame('different-default', $requestWithoutAttribute->getAttribute('name', 'different-default'));
    }
}
