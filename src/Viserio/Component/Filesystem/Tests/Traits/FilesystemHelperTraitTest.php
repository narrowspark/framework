<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Traits;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Tests\Traits\Fixture\FilesystemHelperTraitClass;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class FilesystemHelperTraitTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    private $trait;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root  = vfsStream::setup();
        $this->trait = new FilesystemHelperTraitClass();
    }

    public function testGetRequireThrowsExceptionOnexisitingFile(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException::class);

        $this->trait->getRequire(vfsStream::url('foo/bar/tmp/file.php'));
    }

    public function testGetRequire(): void
    {
        $file = vfsStream::newFile('pop.php')->withContent('<?php
declare(strict_types=1); return "pop"; ?>')->at($this->root);

        $pop = $this->trait->getRequire($file->url());

        $this->assertSame('pop', $pop);
    }

    public function testIsWritable(): void
    {
        $file = vfsStream::newFile('foo.txt', 0444)->withContent('foo')->at($this->root);

        $this->assertFalse($this->trait->isWritable($file->url()));

        $file->chmod(0777);

        $this->assertTrue($this->trait->isWritable($file->url()));
    }

    public function testIsFile(): void
    {
        $this->root->addChild(new vfsStreamDirectory('assets'));
        $dir  = $this->root->getChild('assets');
        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        $this->assertFalse($this->trait->isFile($dir->url()));
        $this->assertTrue($this->trait->isFile($file->url()));
    }
}
