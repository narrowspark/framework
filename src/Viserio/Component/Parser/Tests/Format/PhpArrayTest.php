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

namespace Viserio\Component\Parser\Tests\Format;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Parser\Dumper\PhpArrayDumper;
use Viserio\Component\Parser\Parser\PhpArrayParser;

/**
 * @internal
 *
 * @small
 */
final class PhpArrayTest extends TestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory */
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
        $expectedArray = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];

        $file = vfsStream::newFile('temp.php')->withContent(
            '<?php
declare(strict_types=1);
return ' . VarExporter::export($expectedArray) . ";\n"
        )->at($this->root);

        $parsed = (new PhpArrayParser())->parse($file->url());

        self::assertSame($expectedArray, $parsed);
    }

    public function testParseToThrowException(): void
    {
        $this->expectException(\Viserio\Contract\Parser\Exception\ParseException::class);
        $this->expectExceptionMessage('No such file [nonexistfile] found.');

        (new PhpArrayParser())->parse('nonexistfile');
    }

    public function testParseToThrowExceptionWithInFileException(): void
    {
        $this->expectException(\Viserio\Contract\Parser\Exception\ParseException::class);
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
        $expectedArray = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
        $file = vfsStream::newFile('temp.php')->withContent(
            "<?php\ndeclare(strict_types=1);\n\nreturn " . VarExporter::export($expectedArray) . ";\n"
        )->at($this->root);

        $dump = vfsStream::newFile('temp2.php')->withContent(
            (new PhpArrayDumper())->dump($expectedArray)
        )->at($this->root);

        self::assertFileEquals($file->url(), $dump->url());
    }
}
