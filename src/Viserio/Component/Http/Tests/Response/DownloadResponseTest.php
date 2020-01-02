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

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response\DownloadResponse;
use Viserio\Component\Http\Tests\Response\Traits\StreamBodyContentCasesTrait;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class DownloadResponseTest extends MockeryTestCase
{
    use StreamBodyContentCasesTrait;

    /** @var string */
    public const VALID_CSV_BODY = <<<'EOF'
"first","last","email","dob",
"john","citizen","john.citizen@afakeemailaddress.com","01/01/1970",
EOF;

    public function testConstructorAcceptsBodyAsString(): void
    {
        $response = new DownloadResponse(self::VALID_CSV_BODY, '', 200, 'text/csv; charset=utf-8');

        self::assertSame(self::VALID_CSV_BODY, (string) $response->getBody());
        self::assertSame(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $status = 404;

        $response = new DownloadResponse(self::VALID_CSV_BODY, '', $status, 'text/csv; charset=utf-8');

        self::assertSame(404, $response->getStatusCode());
        self::assertSame(self::VALID_CSV_BODY, (string) $response->getBody());
    }

    public function testConstructorAllowsSendingDownloadResponse(): void
    {
        $status = 404;
        $filename = 'download.csv';

        $response = new DownloadResponse(self::VALID_CSV_BODY, $filename, $status, 'text/csv; charset=utf-8');

        self::assertSame(
            [
                'cache-control' => ['must-revalidate'],
                'content-description' => ['File Transfer'],
                'content-transfer-encoding' => ['Binary'],
                'expires' => ['0'],
                'pragma' => ['Public'],
                'content-disposition' => [\sprintf('attachment; filename=%s', \basename($filename))],
                'content-type' => ['text/csv; charset=utf-8'],
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

        new DownloadResponse(self::VALID_CSV_BODY, 'download.csv', 404, 'text/csv; charset=utf-8', [$header => [$value]]);
    }

    /**
     * @return array<int, array<string>>
     */
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
        $contentType = 'text/csv; charset=utf-8';

        $response = new DownloadResponse(self::VALID_CSV_BODY, $filename, $status, $contentType, $headers);

        self::assertSame(['foo-bar'], $response->getHeader('x-custom'));
        self::assertSame($contentType, $response->getHeaderLine('content-type'));
        self::assertSame(404, $response->getStatusCode());
        self::assertSame(self::VALID_CSV_BODY, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\StreamInterface $streamMock */
        $streamMock = $this->mock(StreamInterface::class);

        $response = new DownloadResponse($streamMock, '');

        self::assertSame($streamMock, $response->getBody());
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public function provideRaisesExceptionForNonStringNonStreamBodyContentCases(): iterable
    {
        return $this->getNonStreamBodyContentCases();
    }

    /**
     * @dataProvider provideRaisesExceptionForNonStringNonStreamBodyContentCases
     *
     * @param mixed $body
     */
    public function testRaisesExceptionForNonStringNonStreamBodyContent($body): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DownloadResponse($body, '');
    }

    public function testConstructorRewindsBodyStream(): void
    {
        $response = new DownloadResponse(self::VALID_CSV_BODY, 'csv.csv');

        self::assertSame(self::VALID_CSV_BODY, $response->getBody()->getContents());
    }
}
