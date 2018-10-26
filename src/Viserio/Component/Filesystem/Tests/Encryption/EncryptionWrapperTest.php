<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Encryption;

use ParagonIE\Halite\KeyFactory;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException;
use Viserio\Component\Filesystem\Adapter\LocalConnector;
use Viserio\Component\Filesystem\Encryption\EncryptionWrapper;
use Viserio\Component\Filesystem\FilesystemAdapter;

/**
 * @internal
 */
final class EncryptionWrapperTest extends TestCase
{
    /**
     * @var string
     */
    private $root;

    /**
     * @var \Viserio\Component\Filesystem\Encryption\EncryptionWrapper
     */
    private $adapter;

    /**
     * Setup the environment.
     */
    protected function setUp(): void
    {
        if (\mb_stripos(\PHP_OS, 'win') === 0) {
            $this->markTestSkipped('@Todo fix this test on windows.');
        }

        $this->root = __DIR__ . \DIRECTORY_SEPARATOR . 'stubs';
        $connector  = new LocalConnector(['path' => $this->root]);

        $adapter = $connector->connect();

        $this->adapter = new EncryptionWrapper(
            new FilesystemAdapter(
                $adapter,
                []
            ),
            KeyFactory::generateEncryptionKey()
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (\scandir($this->root) as $file) {
            if (\is_file($this->root . \DIRECTORY_SEPARATOR . $file)) {
                \unlink($this->root . \DIRECTORY_SEPARATOR . $file);
            }
        }

        \rmdir($this->root);
    }

    public function testWriteStream(): void
    {
        $filePath = $this->root . \DIRECTORY_SEPARATOR . 'dummy.text';

        \file_put_contents($filePath, 'dummy');

        $temp = \fopen($filePath, 'rb');

        $this->assertTrue($this->adapter->writeStream('encrypt.txt', $temp));
        $this->assertSame('dummy', \stream_get_contents($this->adapter->readStream('encrypt.txt')));
    }

    public function testWrite(): void
    {
        $this->assertTrue($this->adapter->write('encrypt.txt', 'dummy'));
        $this->assertSame('dummy', $this->adapter->read('encrypt.txt'));
    }

    public function testUpdate(): void
    {
        $this->assertTrue($this->adapter->write('encrypt_update.txt', 'dummy'));
        $this->assertTrue($this->adapter->update('encrypt_update.txt', 'file'));

        $this->assertSame('file', $this->adapter->read('encrypt_update.txt'));
    }

    public function testPut(): void
    {
        $this->assertTrue($this->adapter->put('encrypt_put.txt', 'file'));
        $this->assertSame('file', $this->adapter->read('encrypt_put.txt'));

        $filePath = $this->root . \DIRECTORY_SEPARATOR . 'dummy.text';

        \file_put_contents($filePath, 'dummy');

        $temp = \fopen($filePath, 'rb');

        $this->assertTrue($this->adapter->put('encrypt_put.txt', $temp));
        $this->assertSame('dummy', $this->adapter->read('encrypt_put.txt'));

        $this->assertTrue($this->adapter->put('encrypt_put2.txt', $temp));
        $this->assertSame('dummy', $this->adapter->read('encrypt_put.txt'));
    }

    public function testUpdateStream(): void
    {
        $filePath = $this->root . \DIRECTORY_SEPARATOR . 'dummy.text';

        \file_put_contents($filePath, 'dummy');

        $temp = \fopen($filePath, 'rb');

        $this->assertTrue($this->adapter->updateStream('encrypt_u_stream.txt', $temp));
        $this->assertSame('dummy', $this->adapter->read('encrypt_u_stream.txt'));

        $filePath = $this->root . \DIRECTORY_SEPARATOR . 'dummy.text';

        \file_put_contents($filePath, 'file');

        $temp = \fopen($filePath, 'rb');

        $this->assertTrue($this->adapter->updateStream('encrypt_u_stream.txt', $temp));
        $this->assertSame('file', $this->adapter->read('encrypt_u_stream.txt'));
    }

    public function testRead(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->adapter->read('dont.txt');
    }

    public function testReadStream(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->adapter->readStream('dont.txt');
    }
}
