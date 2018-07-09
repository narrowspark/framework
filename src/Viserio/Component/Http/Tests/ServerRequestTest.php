<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\Http\UploadedFile;

/**
 * @internal
 */
final class ServerRequestTest extends TestCase
{
    public function testUploadedFiles(): void
    {
        $request1 = new ServerRequest('', 'GET');
        $files    = [
            'file'  => new UploadedFile('test', 123, \UPLOAD_ERR_OK),
            'file2' => [
                new UploadedFile('test', 123, \UPLOAD_ERR_OK),
            ],
        ];
        $request2 = $request1->withUploadedFiles($files);

        static::assertNotSame($request2, $request1);
        static::assertSame([], $request1->getUploadedFiles());
        static::assertSame($files, $request2->getUploadedFiles());
    }

    public function testServerParams(): void
    {
        $params  = ['name' => 'value'];
        $request = new ServerRequest('/', 'GET', [], null, '1.1', $params);

        static::assertSame($params, $request->getServerParams());
    }

    public function testCookieParams(): void
    {
        $request1 = new ServerRequest('/', 'GET');
        $params   = ['name' => 'value'];
        $request2 = $request1->withCookieParams($params);

        static::assertNotSame($request2, $request1);
        static::assertEmpty($request1->getCookieParams());
        static::assertSame($params, $request2->getCookieParams());
    }

    public function testQueryParams(): void
    {
        $request1 = new ServerRequest('/', 'GET');
        $params   = ['name' => 'value'];
        $request2 = $request1->withQueryParams($params);

        static::assertNotSame($request2, $request1);
        static::assertEmpty($request1->getQueryParams());
        static::assertSame($params, $request2->getQueryParams());
    }

    public function testParsedBody(): void
    {
        $request1 = new ServerRequest('/', 'GET');
        $params   = ['name' => 'value'];
        $request2 = $request1->withParsedBody($params);

        static::assertNotSame($request2, $request1);
        static::assertEmpty($request1->getParsedBody());
        static::assertSame($params, $request2->getParsedBody());
    }

    public function testAttributes(): void
    {
        $request1 = new ServerRequest('/', 'GET');
        $request2 = $request1->withAttribute('name', 'value');
        $request3 = $request2->withAttribute('other', 'otherValue');
        $request4 = $request3->withoutAttribute('other');
        $request5 = $request3->withoutAttribute('unknown');

        static::assertNotSame($request2, $request1);
        static::assertNotSame($request3, $request2);
        static::assertNotSame($request4, $request3);
        static::assertNotSame($request5, $request4);
        static::assertEmpty($request1->getAttributes());
        static::assertEmpty($request1->getAttribute('name'));
        static::assertEquals(
            'something',
            $request1->getAttribute('name', 'something'),
            'Should return the default value'
        );
        static::assertEquals('value', $request2->getAttribute('name'));
        static::assertEquals(['name' => 'value'], $request2->getAttributes());
        static::assertEquals(['name' => 'value', 'other' => 'otherValue'], $request3->getAttributes());
        static::assertEquals(['name' => 'value'], $request4->getAttributes());
    }

    public function testNullAttribute(): void
    {
        $request = (new ServerRequest('/', 'GET'))->withAttribute('name', null);

        static::assertSame(['name' => null], $request->getAttributes());
        static::assertNull($request->getAttribute('name', 'different-default'));

        $requestWithoutAttribute = $request->withoutAttribute('name');

        static::assertSame([], $requestWithoutAttribute->getAttributes());
        static::assertSame('different-default', $requestWithoutAttribute->getAttribute('name', 'different-default'));
    }
}
