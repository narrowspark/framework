<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\XmlDumper;
use Viserio\Component\Parser\Parser\XmlParser;

/**
 * @internal
 */
final class XmlTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testParse(): void
    {
        $file = vfsStream::newFile('temp.xml')->withContent(
            '<?xml version="1.0"?>
<data>
  <to>Tove</to>
  <from>Jani</from>
  <heading>Reminder</heading>
</data>
            '
        )->at($this->root);

        $parsed = (new XmlParser())->parse(\file_get_contents($file->url()));

        static::assertSame(['to' => 'Tove', 'from' => 'Jani', 'heading' => 'Reminder'], $parsed);
    }

    public function testParseToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\ParseException::class);
        $this->expectExceptionMessage('[ERROR 4] Start tag expected, \'<\' not found (in n/a - line 1, column 1)');

        (new XmlParser())->parse('nonexistfile');
    }

    public function testDump(): void
    {
        $array = [
            'Good guy' => [
                'name'   => 'Luke Skywalker',
                'weapon' => 'Lightsaber',
            ],
            'Bad guy' => [
                'name'   => 'Sauron',
                'weapon' => 'Evil Eye',
            ],
        ];

        $file = vfsStream::newFile('temp.xml')->withContent('<?xml version="1.0"?>
<root><Good_guy><name>Luke Skywalker</name><weapon>Lightsaber</weapon></Good_guy><Bad_guy><name>Sauron</name><weapon>Evil Eye</weapon></Bad_guy></root>
')->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent((new XmlDumper())->dump($array))->at($this->root);

        static::assertEquals(\str_replace("\r\n", '', \file_get_contents($file->url())), \str_replace("\r\n", '', \file_get_contents($dump->url())));
    }

    public function testItCanHandleAnEmptyArray(): void
    {
        static::assertSame('<?xml version="1.0"?>
<root/>
', (new XmlDumper())->dump([]));
    }

    public function testItCanReceiveNameForTheRootElement(): void
    {
        static::assertSame('<?xml version="1.0"?>
<helloyouluckpeople/>
', (new XmlDumper())->dump(['root' => 'helloyouluckpeople']));
    }

    public function testItCanReceiveNameFromArrayForTheRootElement(): void
    {
        static::assertSame('<?xml version="1.0"?>
<helloyouluckpeople/>
', (new XmlDumper())->dump(['root' => ['rootElementName' => 'helloyouluckpeople']]));
    }

    public function testItCanConvertAttributesToXmlForTheRootElement(): void
    {
        static::assertSame('<?xml version="1.0"?>
<root xmlns="https://github.com/narrowspark"/>
', (new XmlDumper())->dump(['root' => ['_attributes' => ['xmlns' => 'https://github.com/narrowspark']]]));
    }

    public function testRootElementAttributesCanAlsoBeSetInSimpleXmlElementStyle(): void
    {
        static::assertSame('<?xml version="1.0"?>
<root xmlns="https://github.com/narrowspark"/>
', (new XmlDumper())->dump(['root' => ['@attributes' => ['xmlns' => 'https://github.com/narrowspark']]]));
    }

    public function testDumpToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\DumpException::class);

        (new XmlDumper())->dump(['tom & jerry' => 'cartoon characters']);
    }

    public function testItCanHandleValuesAsBasicCollection(): void
    {
        static::assertSame('<?xml version="1.0"?>
<root><user>one</user><user>two</user><user>three</user></root>
', (new XmlDumper())->dump(['user' => ['one', 'two', 'three']]));
    }

    public function testItAcceptsAnXmlEncodingType(): void
    {
        static::assertSame('<?xml version="1.0" encoding="UTF-8"?>
<root><user>one</user></root>
', (new XmlDumper())->dump(['user' => 'one', 'encoding' => 'UTF-8']));
    }

    public function testItAcceptsAnXmlVersion(): void
    {
        static::assertSame('<?xml version="1.1"?>
<root><user>one</user></root>
', (new XmlDumper())->dump(['user' => 'one', 'version' => '1.1']));
    }

    public function testItwillRaiseAnExceptionWhenConvertingAnArrayWithInvalidCharactersKeyNames(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\DumpException::class);

        (new XmlDumper())->dump(['one', 'two']);
    }

    public function testItCanHandleValuesAsCollection(): void
    {
        static::assertSame('<?xml version="1.0"?>
<root><user><name>een</name><age>10</age></user><user><name>een</name><age>10</age></user><user><name>twee</name><age>12</age></user></root>
', (new XmlDumper())->dump([
            'user' => [
                [
                    'name' => 'een',
                    'age'  => 10,
                ],
                [
                    'name' => 'twee',
                    'age'  => 12,
                ],
            ],
        ]));
    }

    public function testItWillRaiseAnExceptionWhenValueContainsMixedSquentialArray(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\DumpException::class);
        $this->expectExceptionMessage('Invalid Character Error.');

        (new XmlDumper())->dump([
            'user' => [
                [
                    'name' => 'een',
                    'age'  => 10,
                ],
                'twee' => [
                    'name' => 'twee',
                    'age'  => 12,
                ],
            ],
        ]);
    }

    public function testItCanHandleValuesWithSpecialCharacters(): void
    {
        static::assertSame('<?xml version="1.0"?>
<root><name>this &amp; that</name></root>
', (new XmlDumper())->dump(['name' => 'this & that']));
    }

    public function testItCanGroupByValuesWhenValuesAreInANumericArray(): void
    {
        static::assertSame('<?xml version="1.0"?>
<root><user>foo</user><user>bar</user></root>
', (new XmlDumper())->dump(['user' => ['foo', 'bar']]));
    }

    public function testItCanConvertAttributesToXml(): void
    {
        $array = [
            'Good guy' => [
                'name'        => 'Luke Skywalker',
                'weapon'      => 'Lightsaber',
                '_attributes' => ['nameType' => 1],
            ],
            'Bad guy' => [
                'name'   => 'Sauron',
                'weapon' => 'Evil Eye',
            ],
        ];

        $file = vfsStream::newFile('temp.xml')->withContent('<?xml version="1.0"?>
<root><Good_guy nameType="1"><name>Luke Skywalker</name><weapon>Lightsaber</weapon></Good_guy><Bad_guy><name>Sauron</name><weapon>Evil Eye</weapon></Bad_guy></root>
')->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent((new XmlDumper())->dump($array))->at($this->root);

        static::assertEquals(\str_replace("\r\n", '', \file_get_contents($file->url())), \str_replace("\r\n", '', \file_get_contents($dump->url())));
    }

    public function testItCanHandleAttributesAsCollection(): void
    {
        $array = [
            'user' => [
                [
                    '_attributes' => [
                        'name' => 'een',
                        'age'  => 10,
                    ],
                ],
                [
                    '_attributes' => [
                        'name' => 'twee',
                        'age'  => 12,
                    ],
                ],
            ],
        ];

        $file = vfsStream::newFile('temp.xml')->withContent('<?xml version="1.0"?>
<root><user name="een" age="10"/><user name="een" age="10"/><user name="twee" age="12"/></root>
')->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent((new XmlDumper())->dump($array))->at($this->root);

        static::assertEquals(\str_replace("\r\n", '', \file_get_contents($file->url())), \str_replace("\r\n", '', \file_get_contents($dump->url())));
    }

    public function testItCanConvertAttributesToXmlInSimpleXmlElementStyle(): void
    {
        $array = [
            'Good guy' => [
                'name'        => 'Luke Skywalker',
                'weapon'      => 'Lightsaber',
                '@attributes' => ['nameType' => 1],
            ],
            'Bad guy' => [
                'name'   => 'Sauron',
                'weapon' => 'Evil Eye',
            ],
        ];

        $file = vfsStream::newFile('temp.xml')->withContent('<?xml version="1.0"?>
<root><Good_guy nameType="1"><name>Luke Skywalker</name><weapon>Lightsaber</weapon></Good_guy><Bad_guy><name>Sauron</name><weapon>Evil Eye</weapon></Bad_guy></root>
')->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent((new XmlDumper())->dump($array))->at($this->root);

        static::assertEquals(\str_replace("\r\n", '', \file_get_contents($file->url())), \str_replace("\r\n", '', \file_get_contents($dump->url())));
    }

    public function testItCanHandleAttributesAsCollectionInSimpleXmlElementStyle(): void
    {
        $array = [
            'user' => [
                [
                    '@attributes' => [
                        'name' => 'een',
                        'age'  => 10,
                    ],
                ],
                [
                    '@attributes' => [
                        'name' => 'twee',
                        'age'  => 12,
                    ],
                ],
            ],
        ];

        $file = vfsStream::newFile('temp.xml')->withContent('<?xml version="1.0"?>
<root><user name="een" age="10"/><user name="een" age="10"/><user name="twee" age="12"/></root>
')->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent((new XmlDumper())->dump($array))->at($this->root);

        static::assertEquals(\str_replace("\r\n", '', \file_get_contents($file->url())), \str_replace("\r\n", '', \file_get_contents($dump->url())));
    }

    public function testItCanHandleValuesSetWithAttributesWithSpecialCharactersAndWithSimpleXmlElementStyle(): void
    {
        $array = [
            'movie' => [
                [
                    'title' => [
                        '_attributes' => ['category' => 'SF'],
                        '_value'      => 'STAR WARS',
                    ],
                ],
                [
                    'title' => [
                        '@attributes' => ['category' => 'Children'],
                        '@value'      => 'tom & jerry',
                    ],
                ],
            ],
        ];

        $file = vfsStream::newFile('temp.xml')->withContent('<?xml version="1.0"?>
<root><movie><title category="SF">STAR WARS</title></movie><movie><title category="SF">STAR WARS</title></movie><movie><title category="Children">tom &amp; jerry</title></movie></root>
')->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent((new XmlDumper())->dump($array))->at($this->root);

        static::assertEquals(\str_replace("\r\n", '', \file_get_contents($file->url())), \str_replace("\r\n", '', \file_get_contents($dump->url())));
    }

    public function testItCanHandlValuesSetAsCdataAndWithSimpleXmlElementStyle(): void
    {
        $array = [
            'movie' => [
                [
                    'title' => [
                        '_attributes' => ['category' => 'SF'],
                        '_cdata'      => '<p>STAR WARS</p>',
                    ],
                ],
                [
                    'title' => [
                        '@attributes' => ['category' => 'Children'],
                        '@cdata'      => '<p>tom & jerry</p>',
                    ],
                ],
            ],
        ];

        $file = vfsStream::newFile('temp.xml')->withContent('<?xml version="1.0"?>
<root><movie><title category="SF"><![CDATA[<p>STAR WARS</p>]]></title></movie><movie><title category="SF"><![CDATA[<p>STAR WARS</p>]]></title></movie><movie><title category="Children"><![CDATA[<p>tom & jerry</p>]]></title></movie></root>
')->at($this->root);

        $dump = vfsStream::newFile('dump.xml')->withContent((new XmlDumper())->dump($array))->at($this->root);

        static::assertEquals(\str_replace("\r\n", '', \file_get_contents($file->url())), \str_replace("\r\n", '', \file_get_contents($dump->url())));
    }
}
