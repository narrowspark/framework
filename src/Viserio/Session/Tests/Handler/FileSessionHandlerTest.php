<?php
namespace Viserio\Session\Tests\Handler;

use org\bovigo\vfs\vfsStream;
use Viserio\Filesystem\Filesystem;
use Viserio\Session\Handler\FileSessionHandler;

class FileSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $root;

    /**
     * @var \Viserio\Session\Handler\FileSessionHandler
     */
    private $handler;

    /**
     * @var \Viserio\Filesystem\Filesystem
     */
    private $files;

    public function setUp()
    {
        $this->root = vfsStream::setup();
        $this->files = new Filesystem();
        $this->handler = new FileSessionHandler(
            $this->files,
            $this->root->url(),
            1
        );

        $this->files->createDirectory(__DIR__ . '/stubs');
    }

    public function tearDown()
    {
        $this->files->deleteDirectory(__DIR__ . '/stubs');
    }

    public function testOpenReturnsTrue()
    {
        $handler = $this->handler;

        $this->assertTrue($handler->open($this->root->url(), 'temp'));
    }

    public function testCloseReturnsTrue()
    {
        $handler = $this->handler;

        $this->assertTrue($handler->close());
    }

    public function testReadExistingSessionReturnsTheData()
    {
        vfsStream::newFile('temp')->withContent('Foo Bar')->at($this->root);

        $handler = $this->handler;

        $this->assertSame('Foo Bar', $handler->read('temp'));
    }

    public function testReadMissingSessionReturnsAnEmptyString()
    {
        vfsStream::newFile('temp')->withContent('Foo Bar')->at($this->root);

        $handler = $this->handler;

        $this->assertSame('', $handler->read('12'));
    }

    public function testWriteSuccessfullyReturnsTrue()
    {
        $handler = new FileSessionHandler(
            $this->files,
            __DIR__ . '/stubs',
            2
        );

        $this->assertTrue($handler->write('write.sess', json_encode(['user_id' => 1])));
    }

    public function testGcSuccessfullyReturnsTrue()
    {
        if (getenv('TRAVIS')) {
            $this->markTestSkipped('FileSessionHandler::gc() dont work on travis. ');
        }

        $handler = new FileSessionHandler(
            $this->files,
            __DIR__ . '/stubs',
            2
        );
        $handler->write('temp.sess', json_encode(['user_id' => 1]));

        $this->assertSame('{"user_id":1}', $handler->read('temp.sess'));

        sleep(2);

        $this->assertTrue($handler->gc(2));
        $this->assertSame('', $handler->read('temp.sess'));
    }

    public function testDestroySuccessfullReturnsTrue()
    {
        vfsStream::newFile('destroy.sess')->withContent('Foo Bar')->at($this->root);

        $handler = $this->handler;

        $this->assertTrue($handler->destroy('destroy.sess'));
    }
}
