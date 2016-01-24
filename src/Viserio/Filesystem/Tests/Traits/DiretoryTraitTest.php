<?php
namespace Viserio\Filesystem\Test\Traits;

use Viserio\Filesystem\Traits\DiretoryTrait;

class DiretoryTraitTest extends \PHPUnit_Framework_TestCase
{
    use DiretoryTrait;

    public function testDeleteDirectory()
    {
        mkdir(__DIR__ . '/foo');
        file_put_contents(__DIR__ . '/foo/file.txt', 'Hello World');

        $this->deleteDirectory(__DIR__ . '/foo');

        $this->assertFalse(is_dir(__DIR__ . '/foo'));
        $this->assertFileNotExists(__DIR__ . '/foo/file.txt');
    }
}
