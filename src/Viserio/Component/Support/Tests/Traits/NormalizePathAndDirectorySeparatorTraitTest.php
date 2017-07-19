<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class NormalizePathAndDirectorySeparatorTraitTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testNormalizeDirectorySeparator(): void
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            self::assertSame('path/to/test', self::normalizeDirectorySeparator('path\to\test'));

            $paths = self::normalizeDirectorySeparator(['path\to\test', 'path\to\test', 'vfs://path/to/test']);
            self::assertSame(['path/to/test', 'path/to/test', 'vfs://path/to/test'], $paths);
        }

        if (DIRECTORY_SEPARATOR === '/') {
            self::assertSame('path/to/test', self::normalizeDirectorySeparator('path/to/test'));
            self::assertSame('vfs://path/to/test', self::normalizeDirectorySeparator('vfs://path/to/test'));
            self::assertSame(
                ['path/to/test', 'path/to/test'],
                self::normalizeDirectorySeparator(['path/to/test', 'path/to/test'])
            );
        }
    }

    /**
     * @expectedException \LogicException
     */
    public function testNormalizePathToThrowException(): void
    {
        self::normalizePath('..//../test/');
    }

    /**
     * @dataProvider  pathProvider
     *
     * @param mixed $input
     * @param mixed $expected
     */
    public function testNormalizePath($input, $expected): void
    {
        $result = self::normalizePath($input);

        self::assertEquals($expected, $result);
    }

    public function pathProvider()
    {
        return [
            ['/dirname/', 'dirname'],
            ['dirname/..', ''],
            ['dirname/../', ''],
            ['dirname./', 'dirname.'],
            ['dirname/./', 'dirname'],
            ['dirname/.', 'dirname'],
            ['./dir/../././', ''],
            ['00004869/files/other/10-75..stl', '00004869/files/other/10-75..stl'],
            ['/dirname//subdir///subsubdir', 'dirname/subdir/subsubdir'],
            ['\dirname\\\\subdir\\\\\\subsubdir', 'dirname\subdir\subsubdir'],
            ['\\\\some\shared\\\\drive', 'some\shared\drive'],
            ['C:\dirname\\\\subdir\\\\\\subsubdir', 'C:\dirname\subdir\subsubdir'],
            ['C:\\\\dirname\subdir\\\\subsubdir', 'C:\dirname\subdir\subsubdir'],
        ];
    }
}
