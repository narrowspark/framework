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

/**
 * @internal
 */
final class DumperTest extends TestCase
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
    protected function setUp(): void
    {
        $this->dumper = new Dumper();
        $this->parser = new Parser();
    }

    public function testDumper(): void
    {
        $this->assertInternalType('string', $this->dumper->dump($this->parser->parse(__DIR__ . '/Fixture/qt/resources.ts'), 'ts'));
    }

    public function testAddNewDumper(): void
    {
        $this->dumper->addMimeType('text/plain', 'txt');
        $this->dumper->addDumper(new TextDumper(), 'txt');

        $this->assertEquals('test', $this->dumper->dump(['test'], 'text/plain'));
    }

    public function testGetParser(): void
    {
        $this->assertInstanceOf(IniDumper::class, $this->dumper->getDumper('ini'));
        $this->assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('json'));
        $this->assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('application/json'));
        $this->assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('application/x-javascript'));
        $this->assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('text/javascript'));
        $this->assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('text/x-javascript'));
        $this->assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('text/x-json'));
        $this->assertInstanceOf(PhpDumper::class, $this->dumper->getDumper('php'));
        $this->assertInstanceOf(SerializeDumper::class, $this->dumper->getDumper('application/vnd.php.serialized'));
        $this->assertInstanceOf(QueryStrDumper::class, $this->dumper->getDumper('application/x-www-form-urlencoded'));
        $this->assertInstanceOf(XmlDumper::class, $this->dumper->getDumper('xml'));
        $this->assertInstanceOf(XmlDumper::class, $this->dumper->getDumper('application/xml'));
        $this->assertInstanceOf(XmlDumper::class, $this->dumper->getDumper('text/xml'));
        $this->assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('yaml'));
        $this->assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('text/yaml'));
        $this->assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('text/x-yaml'));
        $this->assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('application/yaml'));
        $this->assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('application/x-yaml'));
    }

    public function testGetParserToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\NotSupportedException::class);
        $this->expectExceptionMessage('Given extension or mime type [inia] is not supported.');

        $this->dumper->getDumper('inia');
    }
}
