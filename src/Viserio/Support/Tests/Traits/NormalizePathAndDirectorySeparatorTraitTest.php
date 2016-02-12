<?php
namespace Viserio\Support\Tests;

use Viserio\Support\Traits\NormalizePathAndNormalizePathAndDirectorySeparatorTrait;

class NormalizePathAndNormalizePathAndDirectorySeparatorTraitTest extends \PHPUnit_Framework_TestCase
{
    use NormalizePathAndNormalizePathAndDirectorySeparatorTrait;

    public function testNormalizeDirectorySeparator()
    {
        if (DIRECTORY_SEPARATOR !== '/') {
            $this->assertSame('path/to/test', $this->normalizeDirectorySeparator('path\to\test'));

            $paths = $this->normalizeDirectorySeparator(['path\to\test', 'path\to\test', 'vfs://path/to/test']);
            $this->assertSame(['path/to/test', 'path/to/test', 'vfs://path/to/test'], $paths);
        }

        if (DIRECTORY_SEPARATOR === '/') {
            $this->assertSame('path/to/test', $this->normalizeDirectorySeparator('path/to/test'));
            $this->assertSame('vfs://path/to/test', $this->normalizeDirectorySeparator('vfs://path/to/test'));
            $this->assertSame(
                ['path/to/test', 'path/to/test'],
                $this->normalizeDirectorySeparator(['path/to/test', 'path/to/test'])
            );
        }
    }

    /**
     * @dataProvider  pathProvider
     */
    public function testNormalizePath($input, $expected)
    {
        $result = $this->normalizePath($input);

        $this->assertEquals($expected, $result);
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
