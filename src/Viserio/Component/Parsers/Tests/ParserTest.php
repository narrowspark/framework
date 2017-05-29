<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Parsers\Formats\Ini;
use Viserio\Component\Parsers\Formats\Json;
use Viserio\Component\Parsers\Formats\Php;
use Viserio\Component\Parsers\Formats\QueryStr;
use Viserio\Component\Parsers\Formats\Serialize;
use Viserio\Component\Parsers\Formats\Toml;
use Viserio\Component\Parsers\Formats\Xml;
use Viserio\Component\Parsers\Formats\Yaml;
use Viserio\Component\Parsers\Parser;

class ParserTest extends MockeryTestCase
{
    private $parser;

    public function setUp()
    {
        $this->parser = new Parser();
    }

    public function testParserEmptyData()
    {
        self::assertEquals([], $this->parser->parse(''));
    }

    public function testGetParser()
    {
        self::assertInstanceOf(Ini::class, $this->parser->getParser('ini'));
        self::assertInstanceOf(Json::class, $this->parser->getParser('json'));
        self::assertInstanceOf(Json::class, $this->parser->getParser('application/json'));
        self::assertInstanceOf(Json::class, $this->parser->getParser('application/x-javascript'));
        self::assertInstanceOf(Json::class, $this->parser->getParser('text/javascript'));
        self::assertInstanceOf(Json::class, $this->parser->getParser('text/x-javascript'));
        self::assertInstanceOf(Json::class, $this->parser->getParser('text/x-json'));
        self::assertInstanceOf(Php::class, $this->parser->getParser('php'));
        self::assertInstanceOf(Serialize::class, $this->parser->getParser('application/vnd.php.serialized'));
        self::assertInstanceOf(QueryStr::class, $this->parser->getParser('application/x-www-form-urlencoded'));
        self::assertInstanceOf(Toml::class, $this->parser->getParser('toml'));
        self::assertInstanceOf(Xml::class, $this->parser->getParser('xml'));
        self::assertInstanceOf(Xml::class, $this->parser->getParser('application/xml'));
        self::assertInstanceOf(Xml::class, $this->parser->getParser('text/xml'));
        self::assertInstanceOf(Yaml::class, $this->parser->getParser('yaml'));
        self::assertInstanceOf(Yaml::class, $this->parser->getParser('text/yaml'));
        self::assertInstanceOf(Yaml::class, $this->parser->getParser('text/x-yaml'));
        self::assertInstanceOf(Yaml::class, $this->parser->getParser('application/yaml'));
        self::assertInstanceOf(Yaml::class, $this->parser->getParser('application/x-yaml'));
    }

    public function testGetFormat()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')
            ->once()
            ->with('content-type')
            ->andReturn(true);
        $request->shouldReceive('getHeader')
            ->once()
            ->with('content-type')
            ->andReturn(['application/json']);

        $this->parser->setServerRequest($request);

        self::assertEquals('application/json', $this->parser->getFormat());

        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')
            ->once()
            ->with('content-type')
            ->andReturn(false);

        $this->parser->setServerRequest($request);

        self::assertEquals('json', $this->parser->getFormat('json'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\NotSupportedException
     */
    public function testGetParserToThrowException()
    {
        $this->parser->getParser('inia');
    }
}
