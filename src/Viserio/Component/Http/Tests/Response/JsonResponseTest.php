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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\JsonResponse;
use Viserio\Component\Http\Tests\Response\Traits\StreamBodyContentCasesTrait;

/**
 * @internal
 *
 * @small
 */
final class JsonResponseTest extends TestCase
{
    use StreamBodyContentCasesTrait;

    public function testConstructorAcceptsDataAndCreatesJsonEncodedMessageBody(): void
    {
        $data = [
            'nested' => [
                'json' => [
                    'tree',
                ],
            ],
        ];

        $json = '{"nested":{"json":["tree"]}}';
        $response = new JsonResponse($data);

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json; charset=utf-8', $response->getHeaderLine('content-type'));
        self::assertSame($json, (string) $response->getBody());
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public function provideScalarValuePassedToConstructorJsonEncodesDirectlyCases(): iterable
    {
        return $this->getNonStreamBodyContentCases();
    }

    /**
     * @dataProvider provideScalarValuePassedToConstructorJsonEncodesDirectlyCases
     *
     * @param mixed $value
     */
    public function testScalarValuePassedToConstructorJsonEncodesDirectly($value): void
    {
        $response = new JsonResponse($value);

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json; charset=utf-8', $response->getHeaderLine('content-type'));

        $body = (string) $response->getBody();
        $body = \str_replace('php://temp', 'php:\/\/temp', $body);

        // 15 is the default mask used by JsonResponse
        self::assertSame(\json_encode($value, 15), $body);
    }

    public function testCanProvideStatusCodeToConstructor(): void
    {
        $response = new JsonResponse(null, null, 404);

        self::assertEquals(404, $response->getStatusCode());
    }

    public function testCanProvideAlternateContentTypeViaHeadersPassedToConstructor(): void
    {
        $response = new JsonResponse(null, null, 200, ['content-type' => 'foo/json']);

        self::assertEquals('foo/json', $response->getHeaderLine('content-type'));
    }

    public function testJsonErrorHandlingOfResources(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // Serializing something that is not serializable.
        $resource = \fopen('php://memory', 'rb');
        new JsonResponse($resource);
    }

    public function testJsonErrorHandlingOfBadEmbeddedData(): void
    {
        $this->expectException(\Viserio\Contract\Http\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Unable to encode data to JSON in');

        // Serializing something that is not serializable.
        $data = [
            'stream' => \fopen('php://memory', 'rb'),
        ];

        new JsonResponse($data);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideUsesSaneDefaultJsonEncodingFlagsCases(): iterable
    {
        return [
            'uri' => ['https://example.com/foo?bar=baz&baz=bat', 'uri'],
            'html' => ['<p class="test">content</p>', 'html'],
            'string' => ["Don't quote!", 'string'],
        ];
    }

    /**
     * @dataProvider provideUsesSaneDefaultJsonEncodingFlagsCases
     *
     * @param mixed $value
     * @param mixed $key
     */
    public function testUsesSaneDefaultJsonEncodingFlags($value, $key): void
    {
        $defaultFlags = \JSON_HEX_TAG | \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_HEX_AMP | \JSON_UNESCAPED_SLASHES;
        $response = new JsonResponse([$key => $value]);

        $contents = (string) $response->getBody();
        $expected = (string) \json_encode($value, $defaultFlags);

        self::assertStringContainsString(
            $expected,
            $contents,
            \sprintf('Did not encode %s properly; expected (%s), received (%s)', $key, $expected, $contents)
        );
    }

    public function testConstructorRewindsBodyStream(): void
    {
        $json = ['test' => 'data'];
        $response = new JsonResponse($json);
        $actual = \json_decode($response->getBody()->getContents(), true);

        self::assertEquals($json, $actual);
    }
}
