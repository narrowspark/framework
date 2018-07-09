<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\JsonResponse;

/**
 * @internal
 */
final class JsonResponseTest extends TestCase
{
    public function testConstructorAcceptsDataAndCreatesJsonEncodedMessageBody(): void
    {
        $data = [
            'nested' => [
                'json' => [
                    'tree',
                ],
            ],
        ];

        $json     = '{"nested":{"json":["tree"]}}';
        $response = new JsonResponse($data);

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals('application/json; charset=utf-8', $response->getHeaderLine('content-type'));
        static::assertSame($json, (string) $response->getBody());
    }

    public function scalarValuesForJSON()
    {
        return [
            'null'         => [null],
            'false'        => [false],
            'true'         => [true],
            'zero'         => [0],
            'int'          => [1],
            'zero-float'   => [0.0],
            'float'        => [1.1],
            'empty-string' => [''],
            'string'       => ['string'],
        ];
    }

    /**
     * @dataProvider scalarValuesForJSON
     *
     * @param mixed $value
     */
    public function testScalarValuePassedToConstructorJsonEncodesDirectly($value): void
    {
        $response = new JsonResponse($value);

        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals('application/json; charset=utf-8', $response->getHeaderLine('content-type'));
        // 15 is the default mask used by JsonResponse
        static::assertSame(\json_encode($value, 15), (string) $response->getBody());
    }

    public function testCanProvideStatusCodeToConstructor(): void
    {
        $response = new JsonResponse(null, null, 404);

        static::assertEquals(404, $response->getStatusCode());
    }

    public function testCanProvideAlternateContentTypeViaHeadersPassedToConstructor(): void
    {
        $response = new JsonResponse(null, null, 200, ['content-type' => 'foo/json']);

        static::assertEquals('foo/json', $response->getHeaderLine('content-type'));
    }

    public function testJsonErrorHandlingOfResources(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // Serializing something that is not serializable.
        $resource = \fopen('php://memory', 'rb');
        new JsonResponse($resource);
    }

    public function testJsonErrorHandlingOfBadEmbeddedData(): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Unable to encode data to JSON in');

        // Serializing something that is not serializable.
        $data = [
            'stream' => \fopen('php://memory', 'rb'),
        ];

        new JsonResponse($data);
    }

    public function valuesToJsonEncode()
    {
        return [
            'uri'    => ['https://example.com/foo?bar=baz&baz=bat', 'uri'],
            'html'   => ['<p class="test">content</p>', 'html'],
            'string' => ["Don't quote!", 'string'],
        ];
    }

    /**
     * @dataProvider valuesToJsonEncode
     *
     * @param mixed $value
     * @param mixed $key
     */
    public function testUsesSaneDefaultJsonEncodingFlags($value, $key): void
    {
        $defaultFlags = \JSON_HEX_TAG | \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_HEX_AMP | \JSON_UNESCAPED_SLASHES;
        $response     = new JsonResponse([$key => $value]);
        $stream       = $response->getBody();
        $contents     = (string) $stream;
        $expected     = \json_encode($value, $defaultFlags);

        static::assertContains(
            $expected,
            $contents,
            \sprintf('Did not encode %s properly; expected (%s), received (%s)', $key, $expected, $contents)
        );
    }

    public function testConstructorRewindsBodyStream(): void
    {
        $json     = ['test' => 'data'];
        $response = new JsonResponse($json);
        $actual   = \json_decode($response->getBody()->getContents(), true);

        static::assertEquals($json, $actual);
    }
}
