<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Parser\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper;
use Viserio\Component\Parser\Dumper\IniDumper;
use Viserio\Component\Parser\Dumper\JsonDumper;
use Viserio\Component\Parser\Dumper\PhpArrayDumper;
use Viserio\Component\Parser\Dumper\QueryStrDumper;
use Viserio\Component\Parser\Dumper\SerializeDumper;
use Viserio\Component\Parser\Dumper\XmlDumper;
use Viserio\Component\Parser\Dumper\YamlDumper;
use Viserio\Component\Parser\Parser;
use Viserio\Component\Parser\Tests\Fixture\TextDumper;

/**
 * @internal
 *
 * @small
 */
final class DumperTest extends TestCase
{
    /** @var \Viserio\Component\Parser\Parser */
    private $parser;

    /** @var \Viserio\Component\Parser\Dumper */
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
        self::assertNotSame(
            '',
            $this->dumper->dump(
                $this->parser->parse(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'qt' . \DIRECTORY_SEPARATOR . 'resources.ts'),
                'ts'
            )
        );
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
        self::assertInstanceOf(PhpArrayDumper::class, $this->dumper->getDumper('php'));
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

    public function testGetParserToThrowException(): void
    {
        $this->expectException(\Viserio\Contract\Parser\Exception\NotSupportedException::class);
        $this->expectExceptionMessage('Given extension or mime type [inia] is not supported.');

        $this->dumper->getDumper('inia');
    }
}
