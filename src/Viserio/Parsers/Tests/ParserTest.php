<?php
declare(strict_types=1);
namespace Viserio\Parsers\Tests;

use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\Parser;
use Viserio\Parsers\Formats\{
    BSON,
    INI,
    JSON,
    MSGPack,
    PHP,
    QueryStr,
    Serialize,
    TOML,
    XML,
    YAML
};

class ParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp()
    {
        $this->parser = new Parser(new Filesystem());
    }

    public function testParserEmptyData()
    {
        $this->assertEquals([], $this->parser->parse(''));
    }

    public function testGetParser()
    {
        $this->assertInstanceOf(INI::class, $this->parser->getParser('ini'));
        $this->assertInstanceOf(JSON::class, $this->parser->getParser('json'));
        $this->assertInstanceOf(JSON::class, $this->parser->getParser('application/json'));
        $this->assertInstanceOf(JSON::class, $this->parser->getParser('application/x-javascript'));
        $this->assertInstanceOf(JSON::class, $this->parser->getParser('text/javascript'));
        $this->assertInstanceOf(JSON::class, $this->parser->getParser('text/x-javascript'));
        $this->assertInstanceOf(JSON::class, $this->parser->getParser('text/x-json'));
        $this->assertInstanceOf(PHP::class, $this->parser->getParser('php'));
        $this->assertInstanceOf(Serialize::class, $this->parser->getParser('application/vnd.php.serialized'));
        $this->assertInstanceOf(QueryStr::class, $this->parser->getParser('application/x-www-form-urlencoded'));
        $this->assertInstanceOf(TOML::class, $this->parser->getParser('toml'));
        $this->assertInstanceOf(XML::class, $this->parser->getParser('xml'));
        $this->assertInstanceOf(XML::class, $this->parser->getParser('application/xml'));
        $this->assertInstanceOf(XML::class, $this->parser->getParser('text/xml'));
        $this->assertInstanceOf(YAML::class, $this->parser->getParser('yaml'));
        $this->assertInstanceOf(YAML::class, $this->parser->getParser('text/yaml'));
        $this->assertInstanceOf(YAML::class, $this->parser->getParser('text/x-yaml'));
        $this->assertInstanceOf(YAML::class, $this->parser->getParser('application/yaml'));
        $this->assertInstanceOf(YAML::class, $this->parser->getParser('application/x-yaml'));
    }

    public function testGetFormat()
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $this->assertEquals('application/json', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-javascript';
        $this->assertEquals('application/x-javascript', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/javascript';
        $this->assertEquals('text/javascript', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/x-javascript';
        $this->assertEquals('text/x-javascript', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/x-json';
        $this->assertEquals('text/x-json', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        $this->assertEquals('application/x-www-form-urlencoded', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/vnd.php.serialized';
        $this->assertEquals('application/vnd.php.serialized', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/xml';
        $this->assertEquals('application/xml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/xml; charset=utf8';
        $this->assertEquals('application/xml; charset=utf8', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'charset=utf8; application/xml';
        $this->assertEquals('charset=utf8; application/xml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'APPLICATION/XML';
        $this->assertEquals('APPLICATION/XML', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/xml';
        $this->assertEquals('text/xml', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/yaml';
        $this->assertEquals('text/yaml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'text/x-yaml';
        $this->assertEquals('text/x-yaml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/yaml';
        $this->assertEquals('application/yaml', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-yaml';
        $this->assertEquals('application/x-yaml', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/msgpack';
        $this->assertEquals('application/msgpack', $this->parser->getFormat());
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/x-msgpack';
        $this->assertEquals('application/x-msgpack', $this->parser->getFormat());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/bson';
        $this->assertEquals('application/bson', $this->parser->getFormat());

        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }

    /**
     * @expectedException Viserio\Contracts\Parsers\Exception\NotSupportedException
     */
    public function testGetParserToThrowException()
    {
        $this->parser->getParser('inia');
    }
}
