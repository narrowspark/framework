<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Parsers\Dumper;
use Viserio\Component\Parsers\Dumpers\IniDumper;
use Viserio\Component\Parsers\Dumpers\JsonDumper;
use Viserio\Component\Parsers\Dumpers\PhpDumper;
use Viserio\Component\Parsers\Dumpers\QueryStrDumper;
use Viserio\Component\Parsers\Dumpers\SerializeDumper;
use Viserio\Component\Parsers\Dumpers\XmlDumper;
use Viserio\Component\Parsers\Dumpers\YamlDumper;
use Viserio\Component\Parsers\Parser;
use Viserio\Component\Parsers\Tests\Fixtures\TextDumper;

class DumperTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Parsers\Parser
     */
    private $parser;

    /**
     * @var \Viserio\Component\Parsers\Dumper
     */
    private $dumper;

    public function setUp()
    {
        $this->dumper = new Dumper();
        $this->parser = new Parser();
    }

    public function testDumper()
    {
        self::assertTrue(is_string($this->dumper->dump($this->parser->parse(__DIR__ . '/Fixtures/qt/resources.ts'), 'ts')));
    }

    public function testAddNewDumper()
    {
        $this->dumper->addMimeType('text/plain', 'txt');
        $this->dumper->addDumper(new TextDumper(), 'txt');

        self::assertEquals('test', $this->dumper->dump(['test'], 'text/plain'));
    }

    public function testGetParser()
    {
        self::assertInstanceOf(IniDumper::class, $this->dumper->getDumper('ini'));
        self::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('json'));
        self::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('application/json'));
        self::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('application/x-javascript'));
        self::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('text/javascript'));
        self::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('text/x-javascript'));
        self::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('text/x-json'));
        self::assertInstanceOf(PhpDumper::class, $this->dumper->getDumper('php'));
        self::assertInstanceOf(SerializeDumper::class, $this->dumper->getDumper('application/vnd.php.serialized'));
        self::assertInstanceOf(QueryStrDumper::class, $this->dumper->getDumper('application/x-www-form-urlencoded'));
        self::assertInstanceOf(XmlDumper::class, $this->dumper->getDumper('xml'));
        self::assertInstanceOf(XmlDumper::class, $this->dumper->getDumper('application/xml'));
        self::assertInstanceOf(XmlDumper::class, $this->dumper->getDumper('text/xml'));
        self::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('yaml'));
        self::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('text/yaml'));
        self::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('text/x-yaml'));
        self::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('application/yaml'));
        self::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('application/x-yaml'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exceptions\NotSupportedException
     * @expectedExceptionMessage Given extension or mime type [inia] is not supported.
     */
    public function testGetParserToThrowException()
    {
        $this->dumper->getDumper('inia');
    }
}
