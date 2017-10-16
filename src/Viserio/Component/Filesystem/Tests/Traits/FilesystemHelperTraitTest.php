<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Traits;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Tests\Traits\Fixture\FilesystemHelperTraitClass;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FilesystemHelperTraitTest extends TestCase
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
    public function setUp(): void
    {
        $this->root  = vfsStream::setup();
        $this->trait = new FilesystemHelperTraitClass();
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     */
    public function testGetRequireThrowsExceptionOnexisitingFile(): void
    {
        $this->trait->getRequire(vfsStream::url('foo/bar/tmp/file.php'));
    }

    public function testGetRequire(): void
    {
        $file = vfsStream::newFile('pop.php')->withContent('<?php
declare(strict_types=1); return "pop"; ?>')->at($this->root);

        $pop = $this->trait->getRequire($file->url());

        self::assertSame('pop', $pop);
    }

    public function testIsWritable(): void
    {
        $file = vfsStream::newFile('foo.txt', 0444)->withContent('foo')->at($this->root);

        self::assertFalse($this->trait->isWritable($file->url()));

        $file->chmod(0777);

        self::assertTrue($this->trait->isWritable($file->url()));
    }

    public function testIsFile(): void
    {
        $this->root->addChild(new vfsStreamDirectory('assets'));
        $dir  = $this->root->getChild('assets');
        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        self::assertFalse($this->trait->isFile($dir->url()));
        self::assertTrue($this->trait->isFile($file->url()));
    }
}
