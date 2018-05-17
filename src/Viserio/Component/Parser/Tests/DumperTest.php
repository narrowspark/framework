<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper;
use Viserio\Component\Parser\Dumper\IniDumper;
use Viserio\Component\Parser\Dumper\JsonDumper;
use Viserio\Component\Parser\Dumper\PhpDumper;
use Viserio\Component\Parser\Dumper\QueryStrDumper;
use Viserio\Component\Parser\Dumper\SerializeDumper;
use Viserio\Component\Parser\Dumper\XmlDumper;
use Viserio\Component\Parser\Dumper\YamlDumper;
use Viserio\Component\Parser\Parser;
use Viserio\Component\Parser\Tests\Fixture\TextDumper;

class DumperTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parser\Parser
     */
    private $parser;

    /**
     * @var \Viserio\Component\Parser\Dumper
     */
    private $dumper;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->dumper = new Dumper();
        $this->parser = new Parser();
    }

    public function testDumper(): void
    {
        self::assertInternalType('string', $this->dumper->dump($this->parser->parse(__DIR__ . '/Fixture/qt/resources.ts'), 'ts'));
    }

    public function testAddNewDumper(): void
    {
        $this->dumper->addMimeType('text/plain', 'txt');
        $this->dumper->addDumper(new TextDumper(), 'txt');

        self::assertEquals('test', $this->dumper->dump(['test'], 'text/plain'));
    }

    public function testGetParser(): void
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
     * @expectedException \Viserio\Component\Contract\Parser\Exception\NotSupportedException
     * @expectedExceptionMessage Given extension or mime type [inia] is not supported.
     */
    public function testGetParserToThrowException(): void
    {
        $this->dumper->getDumper('inia');
    }
}
