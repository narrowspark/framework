<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Parser\Exception\NotSupportedException;
use Viserio\Component\Parser\Parser;
use Viserio\Component\Parser\Parser\IniParser;
use Viserio\Component\Parser\Parser\JsonParser;
use Viserio\Component\Parser\Parser\PhpArrayParser;
use Viserio\Component\Parser\Parser\QueryStrParser;
use Viserio\Component\Parser\Parser\SerializeParser;
use Viserio\Component\Parser\Parser\TomlParser;
use Viserio\Component\Parser\Parser\XmlParser;
use Viserio\Component\Parser\Parser\YamlParser;
use Viserio\Component\Parser\Tests\Fixture\TextParser;

/**
 * @internal
 */
final class ParserTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parser\Parser
     */
    private $parser;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    public function testParser(): void
    {
        $this->assertEquals([], $this->parser->parse(''));

        $this->assertInternalType('array', $this->parser->parse(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'qt' . \DIRECTORY_SEPARATOR . 'resources.ts'));
        $this->assertInternalType('array', $this->parser->parse(\json_encode(['foo' => 'bar'])));
        $this->assertInternalType('array', $this->parser->parse(\file_get_contents(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'encoding_xliff_v1.xlf')));
    }

    public function testAddNewParser(): void
    {
        $this->parser->addMimeType('text/plain', 'txt');
        $this->parser->addParser(new TextParser(), 'txt');

        $this->assertEquals(['test'], $this->parser->parse('test'));
        $this->assertInstanceOf(TextParser::class, $this->parser->getParser('text/plain'));
    }

    public function testGetParser(): void
    {
        $this->assertInstanceOf(IniParser::class, $this->parser->getParser('ini'));
        $this->assertInstanceOf(JsonParser::class, $this->parser->getParser('json'));
        $this->assertInstanceOf(JsonParser::class, $this->parser->getParser('application/json'));
        $this->assertInstanceOf(JsonParser::class, $this->parser->getParser('application/x-javascript'));
        $this->assertInstanceOf(JsonParser::class, $this->parser->getParser('text/javascript'));
        $this->assertInstanceOf(JsonParser::class, $this->parser->getParser('text/x-javascript'));
        $this->assertInstanceOf(JsonParser::class, $this->parser->getParser('text/x-json'));
        $this->assertInstanceOf(PhpArrayParser::class, $this->parser->getParser('php'));
        $this->assertInstanceOf(SerializeParser::class, $this->parser->getParser('application/vnd.php.serialized'));
        $this->assertInstanceOf(QueryStrParser::class, $this->parser->getParser('application/x-www-form-urlencoded'));
        $this->assertInstanceOf(TomlParser::class, $this->parser->getParser('toml'));
        $this->assertInstanceOf(XmlParser::class, $this->parser->getParser('xml'));
        $this->assertInstanceOf(XmlParser::class, $this->parser->getParser('application/xml'));
        $this->assertInstanceOf(XmlParser::class, $this->parser->getParser('text/xml'));
        $this->assertInstanceOf(YamlParser::class, $this->parser->getParser('yaml'));
        $this->assertInstanceOf(YamlParser::class, $this->parser->getParser('text/yaml'));
        $this->assertInstanceOf(YamlParser::class, $this->parser->getParser('text/x-yaml'));
        $this->assertInstanceOf(YamlParser::class, $this->parser->getParser('application/yaml'));
        $this->assertInstanceOf(YamlParser::class, $this->parser->getParser('application/x-yaml'));
    }

    public function testGetParserToThrowException(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Given extension or mime type [inia] is not supported.');

        $this->parser->getParser('inia');
    }
}
