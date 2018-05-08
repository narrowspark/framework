<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\XmlResponse;

class XmlResponseTest extends TestCase
{
    public function testConstructorAcceptsXmlString(): void
    {
        $body     = '<?xml version="1.0"?>
<data>
  <to>Tove</to>
  <from>Jani</from>
  <heading>Reminder</heading>
</data>
            ';
        $response = new XmlResponse($body);

        self::assertSame($body, (string) $response->getBody());
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('text/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }
}