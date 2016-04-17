<?php
namespace Viserio\Parsers\Tests\Formats\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Parsers\Formats\INI;

class INITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Parsers\Formats\INI
     */
    private $format;

    public function setUp()
    {
        $this->root   = vfsStream::setup();
        $this->parser = new INI();
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            '
one = 1
five = 5
animal = BIRD
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['one' => '1', 'five' => '5', 'animal' => 'BIRD'], $parsed);
    }

    public function testParseWithSection()
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            '
[main]

explore=true
[main.sub]

[main.sub.sub]
value=5
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        $this->assertTrue(is_array($parsed));
        $this->assertSame(
            ['main' => ['explore' => '1'], 'main.sub' => [], 'main.sub.sub' => ['value' => '5']],
            $parsed
        );
    }

    /**
     * @expectedException Viserio\Contracts\Parsers\Exception\ParseException
     * #@expectedExceptionMessage Invalid INI provided.
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testDump()
    {
        $dump = $this->parser->dump(['test' => ['value' => true, 'five' => 5]]);
        $expected = <<<EOT
[test]
value=true
five=5

EOT;

        $this->assertEquals($expected, $dump);
    }
}
