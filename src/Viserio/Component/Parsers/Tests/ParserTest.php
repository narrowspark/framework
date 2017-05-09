<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parsers\Formats\Ini;
use Viserio\Component\Parsers\Formats\Json;
use Viserio\Component\Parsers\Formats\Php;
use Viserio\Component\Parsers\Formats\QueryStr;
use Viserio\Component\Parsers\Formats\Serialize;
use Viserio\Component\Parsers\Formats\Toml;
use Viserio\Component\Parsers\Formats\Xml;
use Viserio\Component\Parsers\Formats\Yaml;
use Viserio\Component\Parsers\Parser;

class ParserTest extends TestCase
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
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        self::assertEquals('application/json', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-javascript';
        self::assertEquals('application/x-javascript', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/javascript';
        self::assertEquals('text/javascript', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/x-javascript';
        self::assertEquals('text/x-javascript', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/x-json';
        self::assertEquals('text/x-json', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        self::assertEquals('application/x-www-form-urlencoded', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/vnd.php.serialized';
        self::assertEquals('application/vnd.php.serialized', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/xml';
        self::assertEquals('application/xml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/xml; charset=utf8';
        self::assertEquals('application/xml; charset=utf8', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'charset=utf8; application/xml';
        self::assertEquals('charset=utf8; application/xml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'APPLICATION/XML';
        self::assertEquals('APPLICATION/XML', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/xml';
        self::assertEquals('text/xml', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/yaml';
        self::assertEquals('text/yaml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/x-yaml';
        self::assertEquals('text/x-yaml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/yaml';
        self::assertEquals('application/yaml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-yaml';
        self::assertEquals('application/x-yaml', $this->parser->getFormat());

        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\NotSupportedException
     */
    public function testGetParserToThrowException()
    {
        $this->parser->getParser('inia');
    }
}
