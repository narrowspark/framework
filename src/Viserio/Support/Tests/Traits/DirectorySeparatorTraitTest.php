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
}
