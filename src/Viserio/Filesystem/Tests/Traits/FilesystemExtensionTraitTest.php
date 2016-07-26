<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Traits;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Traits\FilesystemExtensionTrait;

class FilesystemExtensionTraitTest extends \PHPUnit_Framework_TestCase
{
    use FilesystemExtensionTrait;

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

    public function testWithoutExtension()
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);

        $this->assertSame('temp', $this->withoutExtension($file->url(), 'txt'));

        $file = vfsStream::newFile('temp.php')->withContent('Foo Bar')->at($this->root);

        $this->assertSame('temp', $this->withoutExtension($file->url()));
    }

    public function testGetExtensionReturnsExtension()
    {
        $file = vfsStream::newFile('rock.csv')->withContent('pop,rock')->at($this->root);

        $this->assertEquals('csv', $this->getExtension($file->url()));
    }

    public function testChangeExtension()
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);

        $this->assertSame(vfsStream::url('root/temp.php'), $this->changeExtension($file->url(), 'php'));

        $file = vfsStream::newFile('temp2')->withContent('Foo Bar')->at($this->root);

        $this->assertSame(vfsStream::url('root/temp2.php'), $this->changeExtension($file->url(), 'php'));

        $this->assertSame(vfsStream::url('root/temp3/'), $this->changeExtension(vfsStream::url('root/temp3/'), 'php'));
    }
}
