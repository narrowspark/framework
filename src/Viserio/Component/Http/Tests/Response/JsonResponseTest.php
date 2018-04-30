<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\JsonResponse;

class JsonResponseTest extends TestCase
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

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('content-type'));
        self::assertSame($json, (string) $response->getBody());
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

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->getHeaderLine('content-type'));
        // 15 is the default mask used by JsonResponse
        self::assertSame(\json_encode($value, 15), (string) $response->getBody());
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testJsonErrorHandlingOfResources(): void
    {
        // Serializing something that is not serializable.
        $resource = \fopen('php://memory', 'rb');
        new JsonResponse($resource);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Http\Exception\RuntimeException
     * @expectedExceptionMessage Unable to encode data to JSON in
     */
    public function testJsonErrorHandlingOfBadEmbeddedData(): void
    {
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
        $defaultFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES;
        $response     = new JsonResponse([$key => $value]);
        $stream       = $response->getBody();
        $contents     = (string) $stream;
        $expected     = \json_encode($value, $defaultFlags);

        self::assertContains(
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

        self::assertEquals($json, $actual);
    }
}
