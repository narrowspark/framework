<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Viserio\Component\Http\Response\BinaryFileResponse;
use Viserio\Component\Http\Stream;
use Viserio\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Contract\Http\Exception\LogicException;

/**
 * @internal
 *
 * @small
 */
final class BinaryFileResponseTest extends TestCase
{
    public function testConstruction(): void
    {
        $file = \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'README.md';

        $response = new BinaryFileResponse($file, 404, ['X-Header' => 'Foo'], null, true, true);

        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals('Foo', $response->getHeaderLine('X-Header'));
        self::assertTrue($response->hasHeader('ETag'));
        self::assertTrue($response->hasHeader('Last-Modified'));
        self::assertFalse($response->hasHeader('Content-Disposition'));

        $response = new BinaryFileResponse($file, 404, [], BinaryFileResponse::DISPOSITION_INLINE, true, true);

        self::assertEquals(404, $response->getStatusCode());
        self::assertTrue($response->hasHeader('ETag'));
        self::assertTrue($response->hasHeader('Last-Modified'));
        self::assertEquals('inline; filename=README.md', $response->getHeaderLine('Content-Disposition'));
    }

    public function testConstructWithNonAsciiFilename(): void
    {
        $dir = \sys_get_temp_dir();

        \touch($dir . \DIRECTORY_SEPARATOR . 'fööö.html');

        $response = new BinaryFileResponse($dir . \DIRECTORY_SEPARATOR . 'fööö.html', 200, [], 'attachment');

        @\unlink($dir . \DIRECTORY_SEPARATOR . 'fööö.html');

        self::assertSame('fööö.html', $response->getFile()->getFilename());
    }

    public function testWithBody(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The content cannot be set on a BinaryFileResponse instance.');

        $response = new BinaryFileResponse(__FILE__);

        /** @var resource $handler */
        $handler = \fopen('php://temp', 'rb');

        $response->withBody(new Stream($handler));
    }

    public function testSetContentDispositionGeneratesSafeFallbackFilename(): void
    {
        $response = new BinaryFileResponse(__FILE__);
        $response = $response->setContentDisposition(BinaryFileResponse::DISPOSITION_ATTACHMENT, 'föö.html');

        self::assertSame('attachment; filename=f__.html; filename*=utf-8\'\'f%C3%B6%C3%B6.html', $response->getHeaderLine('Content-Disposition'));
    }

    public function testSetContentDispositionGeneratesSafeFallbackFilenameForWronglyEncodedFilename(): void
    {
        $response = new BinaryFileResponse(__FILE__);
        $iso88591EncodedFilename = \utf8_decode('föö.html');
        $response = $response->setContentDisposition(BinaryFileResponse::DISPOSITION_ATTACHMENT, $iso88591EncodedFilename);

        // the parameter filename* is invalid in this case (rawurldecode('f%F6%F6') does not provide a UTF-8 string but an ISO-8859-1 encoded one)
        self::assertSame('attachment; filename=f__.html; filename*=utf-8\'\'f%F6%F6.html', $response->getHeaderLine('Content-Disposition'));
    }

    public function testDeleteFileAfterSend(): void
    {
        $path = __DIR__ . \DIRECTORY_SEPARATOR . 'to_delete';

        \touch($path);

        $realPath = \realpath($path);

        if ($realPath === false) {
            self::fail(\sprintf('failed to create file [%s]', $path));
        }

        $response = new BinaryFileResponse(new SplFileInfo($realPath), 200, ['Content-Type' => 'application/octet-stream']);
        $response->deleteFileAfterSend(true);

        self::assertSame('', (string) $response->getBody());
        self::assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
        self::assertFileNotExists($path);
    }

    public function testSetFileToThrowExceptionOnInvalidContent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid content [stdClass] provided to Viserio\\Component\\Http\\Response\\BinaryFileResponse.');

        $response = new BinaryFileResponse(__FILE__);
        $response->setFile((object) ['test' => 'test']);
    }
}
