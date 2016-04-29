<?php
namespace Viserio\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\Formats\PHP;

class PHPTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Parsers\Formats\PHP
     */
    private $parser;

    /**
     * @var \Viserio\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file = new Filesystem();
        $this->root   = vfsStream::setup();
        $this->parser = new PHP();
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
return [\'a\' => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,];
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        $this->assertTrue(is_array($parsed));
        $this->assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $parsed);
    }

    /**
     * @expectedException Viserio\Contracts\Parsers\Exception\ParseException
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    public function testDump()
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php return array (
\'a\' => 1,
\'b\' => 2,
\'c\' => 3,
\'d\' => 4,
\'e\' => 5,
);')->at($this->root);

        $dump = $this->parser->dump(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]);

        $this->assertSame(
            str_replace(["\r\n", "\r", '~(*BSR_ANYCRLF)\R~'], "\r\n", $this->file->read($file->url())),
            str_replace(["\r\n", "\r", '~(*BSR_ANYCRLF)\R~'], "\r\n", $dump)
        );
    }
}
