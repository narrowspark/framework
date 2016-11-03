<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Traits;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Viserio\Filesystem\Tests\Traits\Fixture\FilesystemHelperTraitClass;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FilesystemHelperTraitTest extends \PHPUnit_Framework_TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    private $trait;

    /**
     * Setup the environment.
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
        $this->trait = new FilesystemHelperTraitClass();
    }

    /**
     * @expectedException \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testGetRequireThrowsExceptionOnexisitingFile()
    {
        $this->trait->getRequire(vfsStream::url('foo/bar/tmp/file.php'));
    }

    public function testGetRequire()
    {
        $file = vfsStream::newFile('pop.php')->withContent('<?php
declare(strict_types=1); return "pop"; ?>')->at($this->root);

        $pop = $this->trait->getRequire($file->url());

        $this->assertSame('pop', $pop);
    }

    public function testIsWritable()
    {
        $file = vfsStream::newFile('foo.txt', 0444)->withContent('foo')->at($this->root);

        $this->assertFalse($this->trait->isWritable($file->url()));

        $file->chmod(0777);

        $this->assertTrue($this->trait->isWritable($file->url()));
    }

    public function testIsFile()
    {
        $this->root->addChild(new vfsStreamDirectory('assets'));
        $dir = $this->root->getChild('assets');
        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        $this->assertFalse($this->trait->isFile($dir->url()));
        $this->assertTrue($this->trait->isFile($file->url()));
    }
}
