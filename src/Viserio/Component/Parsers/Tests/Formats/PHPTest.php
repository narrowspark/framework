<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Formats\PHP;

class PHPTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Parsers\Formats\PHP
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
        $this->parser = new PHP();
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
     */
    public function testParseToThrowException()
    {
        $this->parser->parse('nonexistfile');
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
