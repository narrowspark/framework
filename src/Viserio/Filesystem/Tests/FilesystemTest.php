<?php
namespace Viserio\Filesystem\Test;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\content\LargeFileContent;
use Viserio\Filesystem\Filesystem;
use Viserio\Support\Traits\DirectorySeparatorTrait;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    use DirectorySeparatorTrait;

    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var Viserio\Filesystem\Filesystem
     */
    private $files;

    /**
     * Setup the environment.
     *
     * @return void
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
        $this->files = new Filesystem;
    }

    public function testReadRetrievesFiles()
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);

        $this->assertEquals('Foo Bar', $this->files->read($file->url()));
    }

    public function testPutStoresFiles()
    {
        $file = vfsStream::newFile('temp.txt')->at($this->root);

        $this->files->put($file->url(), 'Hello World');

        $this->assertStringEqualsFile($file->url(), 'Hello World');
    }
}
