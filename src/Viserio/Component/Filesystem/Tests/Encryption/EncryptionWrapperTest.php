<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Encryption;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Filesystem\Adapter\LocalConnector;
use Viserio\Component\Filesystem\Encryption\EncryptionWrapper;
use Viserio\Component\Filesystem\FilesystemAdapter;

class EncryptionWrapperTest extends TestCase
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
    public function setUp(): void
    {
        $this->root = __DIR__ . '/stubs';

        $connector = new LocalConnector();

        $this->adapter = new EncryptionWrapper(
            new FilesystemAdapter(
                $connector->connect(['path' => $this->root]),
                []
            ),
            Key::createNewRandomKey()
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->root);
    }

    public function testWriteStream(): void
    {
        $temp = \tmpfile();
        \fwrite($temp, 'dummy');
        \rewind($temp);

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

        $temp = \tmpfile();
        \fwrite($temp, 'dummy');
        \rewind($temp);

        self::assertTrue($this->adapter->put('encrypt_put.txt', $temp));
        self::assertSame('dummy', $this->adapter->read('encrypt_put.txt'));

        self::assertTrue($this->adapter->put('encrypt_put2.txt', $temp));
        self::assertSame('dummy', $this->adapter->read('encrypt_put.txt'));
    }

    public function testUpdateStream(): void
    {
        $temp = \tmpfile();
        \fwrite($temp, 'dummy');
        \rewind($temp);

        self::assertTrue($this->adapter->updateStream('encrypt_u_stream.txt', $temp));
        self::assertSame('dummy', $this->adapter->read('encrypt_u_stream.txt'));

        $temp = \tmpfile();
        \fwrite($temp, 'file');
        \rewind($temp);

        self::assertTrue($this->adapter->updateStream('encrypt_u_stream.txt', $temp));
        self::assertSame('file', $this->adapter->read('encrypt_u_stream.txt'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testRead(): void
    {
        $this->adapter->read('dont.txt');
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testReadStream(): void
    {
        $this->adapter->readStream('dont.txt');
    }
}
