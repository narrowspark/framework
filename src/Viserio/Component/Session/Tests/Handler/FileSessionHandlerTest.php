<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Session\Handler\FileSessionHandler;

class FileSessionHandlerTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStream
     */
    private $root;

    /**
     * @var \Viserio\Component\Session\Handler\FileSessionHandler
     */
    private $handler;

    /**
     * @var \Viserio\Component\Filesystem\Filesystem
     */
    private $files;

    public function setUp(): void
    {
        $this->root    = vfsStream::setup();
        $this->files   = new Filesystem();
        $this->handler = new FileSessionHandler(
            $this->files,
            $this->root->url(),
            60
        );

        $this->files->createDirectory(__DIR__ . '/stubs');
    }

    public function tearDown(): void
    {
        $this->files->deleteDirectory(__DIR__ . '/stubs');

        parent::tearDown();
    }

    public function testOpenReturnsTrue(): void
    {
        $handler = $this->handler;

        self::assertTrue($handler->open($this->root->url(), 'temp'));
    }

    public function testCloseReturnsTrue(): void
    {
        $handler = $this->handler;

        self::assertTrue($handler->close());
    }

    public function testReadExistingSessionReturnsTheData(): void
    {
        vfsStream::newFile('temp')->withContent('Foo Bar')->at($this->root);

        $handler = $this->handler;

        self::assertSame('Foo Bar', $handler->read('temp'));
    }

    public function testReadMissingSessionReturnsAnEmptyString(): void
    {
        vfsStream::newFile('temp')->withContent('Foo Bar')->at($this->root);

        $handler = $this->handler;

        self::assertSame('', $handler->read('12'));
    }

    public function testWriteSuccessfullyReturnsTrue(): void
    {
        $handler = new FileSessionHandler(
            $this->files,
            __DIR__ . '/stubs',
            120
        );

        self::assertTrue($handler->write('write.sess', \json_encode(['user_id' => 1])));
    }

    public function testGcSuccessfullyReturnsTrue(): void
    {
        if (\getenv('TRAVIS')) {
            $this->markTestSkipped('FileSessionHandler::gc() dont work on travis. ');
        }

        $handler = new FileSessionHandler(
            $this->files,
            __DIR__ . '/stubs',
            120
        );
        $handler->write('temp.sess', \json_encode(['user_id' => 1]));

        self::assertSame('{"user_id":1}', $handler->read('temp.sess'));

        \sleep(2);

        self::assertTrue($handler->gc(2));
        self::assertSame('', $handler->read('temp.sess'));
    }

    public function testDestroySuccessfullReturnsTrue(): void
    {
        vfsStream::newFile('destroy.sess')->withContent('Foo Bar')->at($this->root);

        $handler = $this->handler;

        self::assertTrue($handler->destroy('destroy.sess'));
    }
}
