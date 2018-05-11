<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Parser;
use Viserio\Component\Parser\Parser\IniParser;
use Viserio\Component\Parser\Parser\JsonParser;
use Viserio\Component\Parser\Parser\PhpArrayParser;
use Viserio\Component\Parser\Parser\QueryStrParser;
use Viserio\Component\Parser\Parser\SerializeParser;
use Viserio\Component\Parser\Parser\TomlParser;
use Viserio\Component\Parser\Parser\XmlParser;
use Viserio\Component\Parser\Parser\YamlParser;
use Viserio\Component\Parser\Tests\Fixtures\TextParser;

class ParserTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parser\Parser
     */
    private $parser;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->parser = new Parser();
    }

    public function testParser(): void
    {
        self::assertEquals([], $this->parser->parse(''));

        self::assertInternalType('array', $this->parser->parse(__DIR__ . '/Fixtures/qt/resources.ts'));
        self::assertInternalType('array', $this->parser->parse(\json_encode(['foo' => 'bar'])));
        self::assertInternalType('array', $this->parser->parse(\file_get_contents(__DIR__ . '/Fixtures/xliff/encoding_xliff_v1.xlf')));
    }

    public function testAddNewParser(): void
    {
        $this->parser->addMimeType('text/plain', 'txt');
        $this->parser->addParser(new TextParser(), 'txt');

        self::assertEquals(['test'], $this->parser->parse('test'));
        self::assertInstanceOf(TextParser::class, $this->parser->getParser('text/plain'));
    }

    public function testGetParser(): void
    {
        self::assertInstanceOf(IniParser::class, $this->parser->getParser('ini'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('json'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('application/json'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('application/x-javascript'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('text/javascript'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('text/x-javascript'));
        self::assertInstanceOf(JsonParser::class, $this->parser->getParser('text/x-json'));
        self::assertInstanceOf(PhpArrayParser::class, $this->parser->getParser('php'));
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
     * @expectedException \Viserio\Component\Contract\Parser\Exception\NotSupportedException
     * @expectedExceptionMessage Given extension or mime type [inia] is not supported.
     */
    public function testGetParserToThrowException(): void
    {
        $this->parser->getParser('inia');
    }
}
