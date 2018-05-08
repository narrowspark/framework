<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\BinaryFileResponse;

class BinaryFileResponseTest extends TestCase
{
    public function testConstruction(): void
    {
        $file = __DIR__.'/../../README.md';

        $response = new BinaryFileResponse($file, BinaryFileResponse::DISPOSITION_INLINE, '', 404, true, true, true);

        self::assertEquals(404, $response->getStatusCode());
        self::assertTrue($response->hasHeader('ETag'));
        self::assertTrue($response->hasHeader('Last-Modified'));
        self::assertEquals('inline; filename=README.md', $response->getHeaderLine('Content-Disposition'));
    }
}
