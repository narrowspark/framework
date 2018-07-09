<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Viserio\Component\Http\Response\BinaryFileResponse;
use Viserio\Component\Http\Stream;

/**
 * @internal
 */
final class BinaryFileResponseTest extends TestCase
{
    public function testConstruction(): void
    {
        $file = __DIR__ . '/../../README.md';

        $response = new BinaryFileResponse($file, 404, ['X-Header' => 'Foo'], null, true, true);

        static::assertEquals(404, $response->getStatusCode());
        static::assertEquals('Foo', $response->getHeaderLine('X-Header'));
        static::assertTrue($response->hasHeader('ETag'));
        static::assertTrue($response->hasHeader('Last-Modified'));
        static::assertFalse($response->hasHeader('Content-Disposition'));

        $response = new BinaryFileResponse($file, 404, [], BinaryFileResponse::DISPOSITION_INLINE, true, true);

        static::assertEquals(404, $response->getStatusCode());
        static::assertTrue($response->hasHeader('ETag'));
        static::assertTrue($response->hasHeader('Last-Modified'));
        static::assertEquals('inline; filename=README.md', $response->getHeaderLine('Content-Disposition'));
    }

    public function testConstructWithNonAsciiFilename(): void
    {
        $dir = \sys_get_temp_dir();

        \touch($dir . '/fööö.html');

        $response = new BinaryFileResponse($dir . '/fööö.html', 200, [], 'attachment');

        @\unlink($dir . '/fööö.html');

        static::assertSame('fööö.html', $response->getFile()->getFilename());
    }

    public function testWithBody(): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\LogicException::class);
        $this->expectExceptionMessage('The content cannot be set on a BinaryFileResponse instance.');

        $response = new BinaryFileResponse(__FILE__);
        $response->withBody(new Stream(\fopen('php://temp', 'rb')));
    }

    public function testSetContentDispositionGeneratesSafeFallbackFilename(): void
    {
        $response = new BinaryFileResponse(__FILE__);
        $response = $response->setContentDisposition(BinaryFileResponse::DISPOSITION_ATTACHMENT, 'föö.html');

        static::assertSame('attachment; filename=f__.html; filename*=utf-8\'\'f%C3%B6%C3%B6.html', $response->getHeaderLine('Content-Disposition'));
    }

    public function testSetContentDispositionGeneratesSafeFallbackFilenameForWronglyEncodedFilename(): void
    {
        $response                = new BinaryFileResponse(__FILE__);
        $iso88591EncodedFilename = \utf8_decode('föö.html');
        $response                = $response->setContentDisposition(BinaryFileResponse::DISPOSITION_ATTACHMENT, $iso88591EncodedFilename);

        // the parameter filename* is invalid in this case (rawurldecode('f%F6%F6') does not provide a UTF-8 string but an ISO-8859-1 encoded one)
        static::assertSame('attachment; filename=f__.html; filename*=utf-8\'\'f%F6%F6.html', $response->getHeaderLine('Content-Disposition'));
    }

    public function testDeleteFileAfterSend(): void
    {
        $path = __DIR__ . '/to_delete';

        \touch($path);

        $realPath = \realpath($path);

        static::assertFileExists($realPath);

        $response = new BinaryFileResponse(new SplFileInfo($realPath), 200, ['Content-Type' => 'application/octet-stream']);
        $response->deleteFileAfterSend(true);

        static::assertSame('', (string) $response->getBody());
        static::assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
        static::assertFileNotExists($path);
    }

    public function testSetFileToThrowExceptionOnInvalidContent(): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid content [stdClass] provided to Viserio\\Component\\Http\\Response\\BinaryFileResponse.');

        $response = new BinaryFileResponse(__FILE__);
        $response->setFile((object) ['test' => 'test']);
    }
}
