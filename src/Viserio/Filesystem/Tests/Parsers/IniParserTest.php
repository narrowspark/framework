<?php
namespace Viserio\Filesystem\Tests\Parsers;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Parser\IniParser;

class IniParserTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Filesystem\Parser\IniParser
     */
    private $parser;

    public function setUp()
    {
        $this->root   = vfsStream::setup();
        $this->parser = new IniParser(new Filesystem());
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.ini')->withContent(
            '
                ; This is a sample configuration file
                ; Comments start with ';', as in php.ini

                [first_section]
                one = 1
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
                urls[git] = "http://git.php.net"
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file);

        $this->assertTrue(is_array($parsed));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\LoadingException
     * #@expectedExceptionMessage
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testSupport()
    {
        $file = vfsStream::newFile('temp.ini')->at($this->root);

        $this->assertTrue($this->parser->supports($file));

        $file = vfsStream::newFile('temp.ini.dist')->at($this->root);

        $this->assertTrue($this->parser->supports($file));

        $file = vfsStream::newFile('temp.notsupported')->at($this->root);

        $this->assertFalse($this->parser->supports($file));
    }

    public function testDump()
    {
        # code...
    }
}
