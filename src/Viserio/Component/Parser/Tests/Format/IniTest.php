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

namespace Viserio\Component\Parser\Tests\Formats\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\IniDumper;
use Viserio\Component\Parser\Parser\IniParser;
use Viserio\Contract\Parser\Exception\ParseException;

/**
 * @internal
 *
 * @small
 */
final class IniTest extends TestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $root;

    /** @var array<string, mixed> */
    private $excepted;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->excepted = [
            'first_section' => [
                'one' => true,
                'two' => false,
                'tree' => null,
                'five' => 5,
                'animal' => 'BIRD',
            ],
            'second_section' => [
                'path' => '/usr/local/bin',
                'URL' => 'http://www.example.com/~username',
            ],
            'third_section' => [
                'phpversion' => [
                    5.0,
                    5.1,
                    5.2,
                    5.3,
                ],
                'urls' => [
                    'svn' => 'http://svn.php.net',
                    'git' => 'http://git.php.net',
                ],
            ],
        ];
    }

    public function testParse(): void
    {
        $file = vfsStream::newFile('temp.ini')
            ->withContent('
; This is a sample configuration file
; Comments start with \';\', as in php.ini

[first_section]
one = true
two = false
tree = null
five = 5
animal = BIRD

[second_section]
path = "/usr/local/bin"
URL = "http://www.example.com/~username"

[third_section]
phpversion[] = "5.0"
phpversion[] = "5.1"
phpversion[] = "5.2"
phpversion[] = "5.3"

urls[svn] = "http://svn.php.net"
urls[git] = "http://git.php.net"')
            ->at($this->root);

        $parsed = (new IniParser())->parse((string) \file_get_contents($file->url()));

        self::assertSame($this->excepted, $parsed);
    }

    public function testParseWithSection(): void
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            '
[all]
test= "foo"
bar[]= "baz"
bar[]= "foo"
'
        )->at($this->root);

        $arrayIni = (new IniParser())->parse((string) \file_get_contents($file->url()));

        self::assertEquals('foo', $arrayIni['all']['test']);
        self::assertEquals('baz', $arrayIni['all']['bar'][0]);
        self::assertEquals('foo', $arrayIni['all']['bar'][1]);
    }

    public function testParseWithNestedSection(): void
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            '
bla.foo.bar = foobar
bla.foobar[] = foobarArray
bla.foo.baz[] = foobaz1
bla.foo.baz[] = foobaz2

[main]

explore=true
[main.sub]

[main.sub.sub]
value=5

[third_section]
phpversion[] = 5
phpversion[] = 5.1
phpversion[] = 5.2
phpversion[] = 5.3
'
        )->at($this->root);

        $arrayIni = (new IniParser())->parse((string) \file_get_contents($file->url()));

        self::assertSame('foobar', $arrayIni['bla']['foo']['bar']);
        self::assertSame('foobarArray', $arrayIni['bla']['foobar'][0]);
        self::assertSame('foobaz1', $arrayIni['bla']['foo']['baz'][0]);
        self::assertSame('foobaz2', $arrayIni['bla']['foo']['baz'][1]);
        self::assertTrue($arrayIni['main']['explore']);
        self::assertSame(5, $arrayIni['main']['sub']['sub']['value']);
        self::assertSame(5, $arrayIni['third_section']['phpversion'][0]);
        self::assertSame(5.1, $arrayIni['third_section']['phpversion'][1]);
        self::assertSame(5.2, $arrayIni['third_section']['phpversion'][2]);
        self::assertSame(5.3, $arrayIni['third_section']['phpversion'][3]);
    }

    public function testParseSections(): void
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            "
[production]
env='production'
production_key='foo'
[staging : production]
env='staging'
staging_key='bar'

"
        )->at($this->root);

        $arrayIni = (new IniParser())->parse((string) \file_get_contents($file->url()));

        self::assertEquals('production', $arrayIni['production']['env']);
        self::assertEquals('foo', $arrayIni['production']['production_key']);
        self::assertEquals('staging', $arrayIni['staging : production']['env']);
        self::assertEquals('bar', $arrayIni['staging : production']['staging_key']);
    }

    public function testParseWithoutSections(): void
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            "
[production]
env='production'
production_key='foo'
[staging : production]
env='staging'
staging_key='bar'
"
        )->at($this->root);

        $parser = new IniParser();
        $parser->setProcessSections(false);

        $arrayIni = $parser->parse((string) \file_get_contents($file->url()));

        self::assertEquals('staging', $arrayIni['env']);
        self::assertEquals('foo', $arrayIni['production_key']);
        self::assertEquals('bar', $arrayIni['staging_key']);
    }

    public function testParseIgnoresNestingInSectionNamesWhenSectionsNotProcessed(): void
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            "
[environments.production]
env='production'
production_key='foo'
[environments.staging]
env='staging'
staging_key='bar'
"
        )->at($this->root);

        $parser = new IniParser();
        $parser->setProcessSections(false);

        $arrayIni = $parser->parse((string) \file_get_contents($file->url()));

        self::assertArrayNotHasKey('environments.production', $arrayIni);
        self::assertArrayNotHasKey('environments.staging', $arrayIni);
        self::assertArrayNotHasKey('environments', $arrayIni);
        self::assertArrayNotHasKey('production', $arrayIni);
        self::assertArrayNotHasKey('staging', $arrayIni);
        self::assertEquals('staging', $arrayIni['env']);
        self::assertEquals('foo', $arrayIni['production_key']);
        self::assertEquals('bar', $arrayIni['staging_key']);
    }

    public function testParseToThrowException(): void
    {
        $this->expectException(ParseException::class);

        (new IniParser())->parse('nonexistfile');
    }

    public function testDump(): void
    {
        $dump = (new IniDumper())->dump($this->excepted);
        $file = vfsStream::newFile('temp.ini')
            ->withContent('
[first_section]
one = true
two = false
tree = null
five = 5
animal = "BIRD"

[second_section]
path = "/usr/local/bin"
URL = "http://www.example.com/~username"

[third_section]
phpversion.0 = 5
phpversion.1 = 5.1
phpversion.2 = 5.2
phpversion.3 = 5.3

urls.svn = "http://svn.php.net"
urls.git = "http://git.php.net"')
            ->at($this->root);

        self::assertEquals(\preg_replace('/^\s+|\n|\r|\s+$/m', '', (string) \file_get_contents($file->url())), \preg_replace('/^\s+|\n|\r|\s+$/m', '', $dump));
    }
}
