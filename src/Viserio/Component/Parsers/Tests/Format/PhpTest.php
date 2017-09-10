<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Parsers\Dumper\PhpDumper;
use Viserio\Component\Parsers\Parser\PhpParser;

class PhpTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Contract\Filesystem\Filesystem
     */
    private $file;

    public function setUp(): void
    {
        $this->file   = new Filesystem();
        $this->root   = vfsStream::setup();
    }

    public function testParse(): void
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
declare(strict_types=1);
return [\'a\' => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,];
            '
        )->at($this->root);

        $parsed = (new PhpParser())->parse($file->url());

        self::assertTrue(\is_array($parsed));
        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Parsers\Exception\ParseException
     * @expectedExceptionMessage No such file [nonexistfile] found.
     */
    public function testParseToThrowException(): void
    {
        (new PhpParser())->parse('nonexistfile');
    }

    /**
     * @expectedException \Viserio\Component\Contract\Parsers\Exception\ParseException
     * @expectedExceptionMessage An exception was thrown by file
     */
    public function testParseToThrowExceptionWithInFileException(): void
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
                throw new \Exception();
            '
        )->at($this->root);

        (new PhpParser())->parse($file->url());
    }

    public function testDumpFile(): void
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
declare(strict_types=1);

return [
    \'a\' => 1,
    \'b\' => 2,
    \'c\' => 3,
    \'d\' => 4,
    \'e\' => 5,
];'
        )->at($this->root);

        $dump = vfsStream::newFile('temp2.php')->withContent(
            (new PhpDumper())->dump(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5])
        )->at($this->root);

        self::assertSame($this->file->read($file->url()), $this->file->read($dump->url()));
    }
}
