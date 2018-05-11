<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Encryption;

use ParagonIE\Halite\KeyFactory;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\LocalConnector;
use Viserio\Component\Filesystem\Encryption\EncryptionWrapper;
use Viserio\Component\Filesystem\FilesystemAdapter;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class EncryptionWrapperTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

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
    public function setUp(): void
    {
        if (\mb_strtolower(\mb_substr(PHP_OS, 0, 3)) === 'win') {
            $this->markTestSkipped('@Todo fix this test on windows.');
        }

        $this->root = self::normalizeDirectorySeparator(__DIR__ . '/stubs');
        $connector  = new LocalConnector();

        $adapter = $connector->connect(['path' => $this->root]);

        $this->adapter = new EncryptionWrapper(
            new FilesystemAdapter(
                $adapter,
                []
            ),
            KeyFactory::generateEncryptionKey()
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        foreach (\scandir($this->root) as $file) {
            if (\is_file($this->root . '/' . $file)) {
                \unlink($this->root . '/' . $file);
            }
        }

        \rmdir($this->root);
    }

    public function testWriteStream(): void
    {
        $filePath = self::normalizeDirectorySeparator($this->root . '/dummy.text');

        file_put_contents($filePath, 'dummy');

        $temp = \fopen($filePath, 'rb');

        self::assertTrue($this->adapter->writeStream('encrypt.txt', $temp));
        self::assertSame('dummy', \stream_get_contents($this->adapter->readStream('encrypt.txt')));
    }

    public function testWrite(): void
    {
        self::assertTrue($this->adapter->write('encrypt.txt', 'dummy'));
        self::assertSame('dummy', $this->adapter->read('encrypt.txt'));
    }

    public function testUpdate(): void
    {
        self::assertTrue($this->adapter->write('encrypt_update.txt', 'dummy'));
        self::assertTrue($this->adapter->update('encrypt_update.txt', 'file'));

        self::assertSame('file', $this->adapter->read('encrypt_update.txt'));
    }

    public function testPut(): void
    {
        self::assertTrue($this->adapter->put('encrypt_put.txt', 'file'));
        self::assertSame('file', $this->adapter->read('encrypt_put.txt'));

        $filePath = self::normalizeDirectorySeparator($this->root . '/dummy.text');

        file_put_contents($filePath, 'dummy');

        $temp = \fopen($filePath, 'rb');

        self::assertTrue($this->adapter->put('encrypt_put.txt', $temp));
        self::assertSame('dummy', $this->adapter->read('encrypt_put.txt'));

        self::assertTrue($this->adapter->put('encrypt_put2.txt', $temp));
        self::assertSame('dummy', $this->adapter->read('encrypt_put.txt'));
    }

    public function testUpdateStream(): void
    {
        $filePath = $this->root . '/dummy.text';

        file_put_contents($filePath, 'dummy');

        $temp = \fopen($filePath, 'rb');

        self::assertTrue($this->adapter->updateStream('encrypt_u_stream.txt', $temp));
        self::assertSame('dummy', $this->adapter->read('encrypt_u_stream.txt'));

        $filePath = $this->root . '/dummy.text';

        file_put_contents($filePath, 'file');

        $temp = \fopen($filePath, 'rb');

        self::assertTrue($this->adapter->updateStream('encrypt_u_stream.txt', $temp));
        self::assertSame('file', $this->adapter->read('encrypt_u_stream.txt'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     */
    public function testRead(): void
    {
        $this->adapter->read('dont.txt');
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     */
    public function testReadStream(): void
    {
        $this->adapter->readStream('dont.txt');
    }
}
