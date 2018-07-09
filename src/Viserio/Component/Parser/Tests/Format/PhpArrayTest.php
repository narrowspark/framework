<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\PhpDumper;
use Viserio\Component\Parser\Parser\PhpArrayParser;

/**
 * @internal
 */
final class PhpArrayTest extends TestCase
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
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
declare(strict_types=1);
return [\'a\' => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5,];
            '
        )->at($this->root);

        $parsed = (new PhpArrayParser())->parse($file->url());

        static::assertInternalType('array', $parsed);
        static::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5], $parsed);
    }

    public function testParseToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\ParseException::class);
        $this->expectExceptionMessage('No such file [nonexistfile] found.');

        (new PhpArrayParser())->parse('nonexistfile');
    }

    public function testParseToThrowExceptionWithInFileException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\ParseException::class);
        $this->expectExceptionMessage('An exception was thrown by file');

        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
                throw new \Exception();
            '
        )->at($this->root);

        (new PhpArrayParser())->parse($file->url());
    }

    public function testDumpFile(): void
    {
        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return [' . \PHP_EOL . '    \'a\' => 1,' . \PHP_EOL . '    \'b\' => 2,' . \PHP_EOL . '    \'c\' => 3,' . \PHP_EOL . '    \'d\' => 4,' . \PHP_EOL . '    \'e\' => 5,' . \PHP_EOL . '];' . \PHP_EOL
        )->at($this->root);

        $dump = vfsStream::newFile('temp2.php')->withContent(
            (new PhpDumper())->dump(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5])
        )->at($this->root);

        static::assertSame(\file_get_contents($file->url()), \file_get_contents($dump->url()));
    }
}
