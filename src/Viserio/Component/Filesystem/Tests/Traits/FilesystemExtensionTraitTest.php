<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Traits;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Traits\FilesystemExtensionTrait;

/**
 * @internal
 */
final class FilesystemExtensionTraitTest extends TestCase
{
    use FilesystemExtensionTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testWithoutExtension(): void
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);

        $this->assertSame('temp', $this->withoutExtension($file->url(), 'txt'));

        $file = vfsStream::newFile('temp.php')->withContent('Foo Bar')->at($this->root);

        $this->assertSame('temp', $this->withoutExtension($file->url()));
    }

    public function testGetExtensionReturnsExtension(): void
    {
        $file = vfsStream::newFile('rock.csv')->withContent('pop,rock')->at($this->root);

        $this->assertEquals('csv', $this->getExtension($file->url()));
    }

    public function testChangeExtension(): void
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);

        $this->assertSame(vfsStream::url('root/temp.php'), $this->changeExtension($file->url(), 'php'));

        $file = vfsStream::newFile('temp2')->withContent('Foo Bar')->at($this->root);

        $this->assertSame(vfsStream::url('root/temp2.php'), $this->changeExtension($file->url(), 'php'));

        $this->assertSame(vfsStream::url('root/temp3/'), $this->changeExtension(vfsStream::url('root/temp3/'), 'php'));
    }

    /**
     * Get normalize or prefixed path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getTransformedPath(string $path): string
    {
        if (isset($this->driver)) {
            $prefix = \method_exists($this->driver, 'getPathPrefix') ? $this->driver->getPathPrefix() : '';

            return $prefix . $path;
        }

        return $path;
    }
}
