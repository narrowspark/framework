<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Formats\Php;

class PhpTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parsers\Formats\Php
     */
    private $parser;

    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    private $file;

    public function setUp()
    {
        $this->file   = new Filesystem();
        $this->root   = vfsStream::setup();
        $this->parser = new Php();
    }

    public function testParse()
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
declare(strict_types=1);
return [\'a\' => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,];
            '
        )->at($this->root);

        $parsed = $this->parser->parse($file->url());

        self::assertTrue(is_array($parsed));
        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     * @expectedExceptionMessage No such file [nonexistfile] found.
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Parsers\Exception\ParseException
     * @expectedExceptionMessage An exception was thrown by file
     */
    public function testParseToThrowExceptionWithInFileException()
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
                throw new \Exception();
            '
        )->at($this->root);

        $this->parser->parse($file->url());
    }

    public function testDump()
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
declare(strict_types=1); return array (
\'a\' => 1,
\'b\' => 2,
\'c\' => 3,
\'d\' => 4,
\'e\' => 5,
);'
        )->at($this->root);

        $dump = vfsStream::newFile('temp.php')->withContent(
            $this->parser->dump(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5])
        )->at($this->root);

        self::assertSame($this->file->read($file->url()), $this->file->read($dump->url()));
    }
}
