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
use Viserio\Contract\Parser\Exception\NotSupportedException;

/**
 * @internal
 *
 * @small
 */
final class ParserTest extends TestCase
{
    /** @var \Viserio\Component\Parser\Parser */
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
        self::assertEquals([], $this->parser->parse(''));

        self::assertNotEquals([], $this->parser->parse(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'qt' . \DIRECTORY_SEPARATOR . 'resources.ts'));
        self::assertNotEquals([], $this->parser->parse((string) \json_encode(['foo' => 'bar'])));
        self::assertNotEquals([], $this->parser->parse((string) \file_get_contents(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'xliff' . \DIRECTORY_SEPARATOR . 'encoding_xliff_v1.xlf')));
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

    public function testGetParserToThrowException(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage('Given extension or mime type [inia] is not supported.');

        $this->parser->getParser('inia');
    }
}
