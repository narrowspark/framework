<?php
namespace Viserio\Support\Tests;

use Viserio\Support\Traits\DirectorySeparatorTrait;

class DirectorySeparatorTraitTest extends \PHPUnit_Framework_TestCase
{
    use DirectorySeparatorTrait;

    public function testGetDirectorySeparator()
    {
        $path = $this->getDirectorySeparator('path/to/test');
        $paths = $this->getDirectorySeparator(['path/to/test', 'path/to/test']);

        if (DIRECTORY_SEPARATOR !== '/') {
            $this->assertSame('path\to\test', $path);
            $this->assertSame(['path\to\test', 'path\to\test'], $paths);
        }

        $this->assertSame('path\to\test', $this->getDirectorySeparator('path\to\test'));
        $this->assertSame(
            ['path\to\test', 'path\to\test'],
            $this->getDirectorySeparator(['path\to\test', 'path\to\test'])
        );
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
