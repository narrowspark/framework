<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parsers\Parser;
use Viserio\Component\Parsers\Parser\IniParser;
use Viserio\Component\Parsers\Parser\JsonParser;
use Viserio\Component\Parsers\Parser\PhpParser;
use Viserio\Component\Parsers\Parser\QueryStrParser;
use Viserio\Component\Parsers\Parser\SerializeParser;
use Viserio\Component\Parsers\Parser\TomlParser;
use Viserio\Component\Parsers\Parser\XmlParser;
use Viserio\Component\Parsers\Parser\YamlParser;
use Viserio\Component\Parsers\Tests\Fixtures\TextParser;

class ParserTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parsers\Parser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new Parser();
    }

    public function testParser()
    {
        self::assertEquals([], $this->parser->parse(''));

        self::assertTrue(is_array($this->parser->parse(__DIR__ . '/Fixtures/qt/resources.ts')));
        self::assertTrue(is_array($this->parser->parse(json_encode(['foo' => 'bar']))));
        self::assertTrue(is_array($this->parser->parse(file_get_contents(__DIR__ . '/Fixtures/xliff/encoding_xliff_v1.xlf'))));
    }

    public function testAddNewParser()
    {
        $this->parser->addMimeType('text/plain', 'txt');
        $this->parser->addParser(new TextParser(), 'txt');

        self::assertEquals(['test'], $this->parser->parse('test'));
        self::assertInstanceOf(TextParser::class, $this->parser->getParser('text/plain'));
    }

    public function testGetParser()
    {
        self::assertInstanceOf(IniParser::class, $this->parser->getParser('ini'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('json'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('application/json'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('application/x-javascript'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('text/javascript'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('text/x-javascript'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('text/x-json'));
        self::assertInstanceOf(PhpParser::class, $this->parser->getParser('php'));
        self::assertInstanceOf(SerializeParser::class, $this->parser->getParser('application/vnd.php.serialized'));
        self::assertInstanceOf(QueryStrParser::class, $this->parser->getParser('application/x-www-form-urlencoded'));
        self::assertInstanceOf(TomlParser::class, $this->parser->getParser('toml'));
        self::assertInstanceOf(XmlParser::class, $this->parser->getParser('xml'));
        self::assertInstanceOf(XmlParser::class, $this->parser->getParser('application/xml'));
        self::assertInstanceOf(XmlParser::class, $this->parser->getParser('text/xml'));
        self::assertInstanceOf(YamlParser::class, $this->parser->getParser('yaml'));
        self::assertInstanceOf(YamlParser::class, $this->parser->getParser('text/yaml'));
        self::assertInstanceOf(YamlParser::class, $this->parser->getParser('text/x-yaml'));
        self::assertInstanceOf(YamlParser::class, $this->parser->getParser('application/yaml'));
        self::assertInstanceOf(YamlParser::class, $this->parser->getParser('application/x-yaml'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\NotSupportedException
     * @expectedExceptionMessage Given extension or mime type [inia] is not supported.
     */
    public function testGetParserToThrowException()
    {
        $this->parser->getParser('inia');
    }
}
