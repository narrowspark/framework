<?php
namespace Viserio\Filesystem\Tests\Traits;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Viserio\Filesystem\Traits\FilesystemHelperTrait;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FilesystemHelperTraitTest extends \PHPUnit_Framework_TestCase
{
    use FilesystemHelperTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * Setup the environment.
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testGetRequireThrowsExceptionOnexisitingFile()
    {
        $this->getRequire(vfsStream::url('foo/bar/tmp/file.php'));
    }

    public function testGetRequire()
    {
        $file = vfsStream::newFile('pop.php')->withContent('<?php return "pop"; ?>')->at($this->root);

        $pop = $this->getRequire($file->url());

        $this->assertSame('pop', $pop);
    }

    public function testIsWritable()
    {
        $file = vfsStream::newFile('foo.txt', 0444)->withContent('foo')->at($this->root);

        $this->assertFalse($this->isWritable($file->url()));

        $file->chmod(0777);

        $this->assertTrue($this->isWritable($file->url()));
    }

    public function testIsFile()
    {
        $this->root->addChild(new vfsStreamDirectory('assets'));
        $dir  = $this->root->getChild('assets');
        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        $this->assertFalse($this->isFile($dir->url()));
        $this->assertTrue($this->isFile($file->url()));
    }
}
