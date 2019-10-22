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
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response\CsvResponse;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class CsvResponseTest extends TestCase
{
    public const VALID_CSV_BODY = <<<'EOF'
"first","last","email","dob",
"john","citizen","john.citizen@afakeemailaddress.com","01/01/1970",
EOF;

    public function testConstructorAcceptsBodyAsString(): void
    {
        $response = new CsvResponse(self::VALID_CSV_BODY);

        self::assertSame(self::VALID_CSV_BODY, (string) $response->getBody());
        self::assertSame(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $status = 404;

        $response = new CsvResponse(self::VALID_CSV_BODY, $status);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame(self::VALID_CSV_BODY, (string) $response->getBody());
    }

    public function testConstructorAllowsSendingDownloadResponse(): void
    {
        $status = 404;
        $filename = 'download.csv';

        $response = new CsvResponse(self::VALID_CSV_BODY, $status, $filename);

        self::assertSame(
            [
                'cache-control' => ['must-revalidate'],
                'content-description' => ['File Transfer'],
                'content-disposition' => [\sprintf('attachment; filename=%s', \basename($filename))],
                'content-transfer-encoding' => ['Binary'],
                'content-type' => ['text/csv; charset=utf-8'],
                'expires' => ['0'],
                'pragma' => ['Public'],
            ],
            $response->getHeaders()
        );
        self::assertSame(404, $response->getStatusCode());
        self::assertSame(self::VALID_CSV_BODY, (string) $response->getBody());
    }

    /**
     * @dataProvider provideConstructorDoesNotAllowsOverridingDownloadHeadersWhenSendingDownloadResponseCases
     *
     * @param mixed $header
     * @param mixed $value
     */
    public function testConstructorDoesNotAllowsOverridingDownloadHeadersWhenSendingDownloadResponse(
        $header,
        $value
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot override download headers (cache-control, content-description, content-disposition, content-transfer-encoding, expires, pragma) when download response is being sent'
        );

        new CsvResponse(self::VALID_CSV_BODY, 404, 'download.csv', [$header => [$value]]);
    }

    public function provideConstructorDoesNotAllowsOverridingDownloadHeadersWhenSendingDownloadResponseCases(): iterable
    {
        return [
            ['cache-control', 'must-revalidate'],
            ['content-description', 'File Transfer'],
            ['content-disposition', 'upload.csv'],
            ['content-transfer-encoding', 'Binary'],
            ['expires', '0'],
            ['pragma', 'Public'],
        ];
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $status = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $filename = '';

        $response = new CsvResponse(self::VALID_CSV_BODY, $status, $filename, $headers);

        self::assertSame(['foo-bar'], $response->getHeader('x-custom'));
        self::assertSame('text/csv; charset=utf-8', $response->getHeaderLine('content-type'));
        self::assertSame(404, $response->getStatusCode());
        self::assertSame(self::VALID_CSV_BODY, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $stream = $this->prophesize(StreamInterface::class);
        $body = $stream->reveal();

        $response = new CsvResponse($body);

        self::assertSame($body, $response->getBody());
    }

    public function provideRaisesExceptionforNonStringNonStreamBodyContentCases(): iterable
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'zero' => [0],
            'int' => [1],
            'zero-float' => [0.0],
            'float' => [1.1],
            'array' => [['php://temp']],
            'object' => [(object) ['php://temp']],
        ];
    }

    /**
     * @dataProvider provideRaisesExceptionforNonStringNonStreamBodyContentCases
     *
     * @param mixed $body
     */
    public function testRaisesExceptionforNonStringNonStreamBodyContent($body): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvResponse($body);
    }

    public function testConstructorRewindsBodyStream(): void
    {
        $response = new CsvResponse(self::VALID_CSV_BODY);

        self::assertSame(self::VALID_CSV_BODY, $response->getBody()->getContents());
    }
}
