<?php
namespace Viserio\Filesystem\Tests\Parsers;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Parser\IniParser;

class PhpParserTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Filesystem\Parser\PhpParser
     */
    private $parser;

    public function setUp()
    {
        $this->root   = vfsStream::setup();
        $this->parser = new PhpParser(new Filesystem());
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
                return ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,];
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file);

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5,], $parsed);
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
        $file = vfsStream::newFile('temp.php')->at($this->root);

        $this->assertTrue($this->parser->supports($file));

        $file = vfsStream::newFile('temp.notsupported')->at($this->root);

        $this->assertFalse($this->parser->supports($file));
    }

    public function testDump()
    {
        # code...
    }
}
