<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Session\Handler\FileSessionHandler;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class FileSessionHandlerTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var \Viserio\Component\Session\Handler\FileSessionHandler
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root    = vfsStream::setup();
        $this->handler = new FileSessionHandler(
            $this->root->url(),
            60
        );
    }

    public function testInstanceOf(): void
    {
        $this->assertInstanceOf(\SessionHandlerInterface::class, $this->handler);
        $this->assertInstanceOf(\SessionUpdateTimestampHandlerInterface::class, $this->handler);
    }

    public function testOpenReturnsTrue(): void
    {
        $this->assertTrue($this->handler->open($this->root->url(), 'temp'));
    }

    public function testCloseReturnsTrue(): void
    {
        $this->assertTrue($this->handler->close());
    }

    public function testReadExistingSessionReturnsTheData(): void
    {
        vfsStream::newFile('temp.' . FileSessionHandler::FILE_EXTENSION)
            ->withContent('Foo Bar')
            ->at($this->root);

        $this->assertSame('Foo Bar', $this->handler->read('temp'));
    }

    public function testReadMissingSessionReturnsAnEmptyString(): void
    {
        vfsStream::newFile('temp')
            ->withContent('Foo Bar')
            ->at($this->root);

        $this->assertSame('', $this->handler->read('12'));
    }

    public function testWriteSuccessfullyReturnsTrue(): void
    {
        $dir = self::normalizeDirectorySeparator(__DIR__ . '/' . __FUNCTION__);

        \mkdir($dir);

        $handler = new FileSessionHandler($dir, 120);

        $this->assertTrue($handler->write('write', \json_encode(['user_id' => 1])));

        \unlink(self::normalizeDirectorySeparator($dir . '\write.' . FileSessionHandler::FILE_EXTENSION));
        \rmdir($dir);
    }

    public function testGcSuccessfullyReturnsTrue(): void
    {
        $dir = self::normalizeDirectorySeparator(__DIR__ . '/' . __FUNCTION__);

        @\mkdir($dir);

        $handler = new FileSessionHandler($dir, 2);
        $handler->write('temp', \json_encode(['user_id' => 1]));

        $this->assertSame('{"user_id":1}', $handler->read('temp'));

        \sleep(3);

        $this->assertTrue($handler->gc(2));
        $this->assertSame('', $handler->read('temp'));

        \rmdir($dir);
    }

    public function testDestroySuccessfullReturnsTrue(): void
    {
        vfsStream::newFile('destroy.' . FileSessionHandler::FILE_EXTENSION)
            ->withContent('Foo Bar')
            ->at($this->root);

        $this->assertTrue($this->handler->destroy('destroy'));
    }

    public function testUpdateTimestamp(): void
    {
        $dir = self::normalizeDirectorySeparator(__DIR__ . '/' . __FUNCTION__);

        \mkdir($dir);

        $lifetime = 120;
        $handler  = new FileSessionHandler($dir, $lifetime);

        $filePath = self::normalizeDirectorySeparator($dir . '\update.' . FileSessionHandler::FILE_EXTENSION);

        $handler->write('update', \json_encode(['user_id' => 1]));

        $this->assertTrue($handler->updateTimestamp('update', 'no'));

        \unlink($filePath);
        \rmdir($dir);
    }
}
