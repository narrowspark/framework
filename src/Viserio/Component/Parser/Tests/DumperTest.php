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
        static::assertInternalType('string', $this->dumper->dump($this->parser->parse(__DIR__ . '/Fixture/qt/resources.ts'), 'ts'));
    }

    public function testAddNewDumper(): void
    {
        $this->dumper->addMimeType('text/plain', 'txt');
        $this->dumper->addDumper(new TextDumper(), 'txt');

        static::assertEquals('test', $this->dumper->dump(['test'], 'text/plain'));
    }

    public function testGetParser(): void
    {
        static::assertInstanceOf(IniDumper::class, $this->dumper->getDumper('ini'));
        static::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('json'));
        static::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('application/json'));
        static::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('application/x-javascript'));
        static::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('text/javascript'));
        static::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('text/x-javascript'));
        static::assertInstanceOf(JsonDumper::class, $this->dumper->getDumper('text/x-json'));
        static::assertInstanceOf(PhpDumper::class, $this->dumper->getDumper('php'));
        static::assertInstanceOf(SerializeDumper::class, $this->dumper->getDumper('application/vnd.php.serialized'));
        static::assertInstanceOf(QueryStrDumper::class, $this->dumper->getDumper('application/x-www-form-urlencoded'));
        static::assertInstanceOf(XmlDumper::class, $this->dumper->getDumper('xml'));
        static::assertInstanceOf(XmlDumper::class, $this->dumper->getDumper('application/xml'));
        static::assertInstanceOf(XmlDumper::class, $this->dumper->getDumper('text/xml'));
        static::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('yaml'));
        static::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('text/yaml'));
        static::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('text/x-yaml'));
        static::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('application/yaml'));
        static::assertInstanceOf(YamlDumper::class, $this->dumper->getDumper('application/x-yaml'));
    }

    public function testGetParserToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\NotSupportedException::class);
        $this->expectExceptionMessage('Given extension or mime type [inia] is not supported.');

        $this->dumper->getDumper('inia');
    }
}
