<?php
namespace Viserio\Filesystem\Test\Traits;

use Viserio\Filesystem\Traits\MimetypeTrait;

class MimetypeTraitTest extends \PHPUnit_Framework_TestCase
{
    use MimetypeTrait;

    public function testTypeIndentifiesFile()
    {
        file_put_contents(__DIR__ . '/foo.txt', 'foo');

        $this->assertEquals('text/plain', $this->mimeType(__DIR__ . '/foo.txt'));

        @unlink(__DIR__ . '/foo.txt');
    }
}
