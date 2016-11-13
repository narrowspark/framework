<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Encryption;

use Defuse\Crypto\Key;
use Viserio\Filesystem\Adapters\LocalConnector;
use Viserio\Filesystem\Encryption\EncryptionWrapper;
use Viserio\Filesystem\FilesystemAdapter;

class EncryptionWrapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $root;

    /**
     * @var \Viserio\Filesystem\FilesystemAdapter
     */
    private $adapter;

    /**
     * Setup the environment.
     */
    public function setUp()
    {
        $this->root = __DIR__ . '/stubs';

        $connector = new LocalConnector();

        $this->adapter = new EncryptionWrapper(
            new FilesystemAdapter(
                $connector->connect(['path' => $this->root])
            ),
            Key::createNewRandomKey()
        );
    }

    public function tearDown()
    {
        $this->delTree($this->root);
    }

    public function testWriteStream()
    {
        $temp = tmpfile();
        fwrite($temp, 'dummy');
        rewind($temp);

        $this->assertTrue($this->adapter->writeStream('encrypt.txt', $temp));
        $this->assertSame('dummy', stream_get_contents($this->adapter->readStream('encrypt.txt')));
    }

    public function testWrite()
    {
        $this->assertTrue($this->adapter->write('encrypt.txt', 'dummy'));
        $this->assertSame('dummy', $this->adapter->read('encrypt.txt'));
    }

    public function testUpdate()
    {
        $this->assertTrue($this->adapter->write('encrypt_update.txt', 'dummy'));
        $this->assertTrue($this->adapter->update('encrypt_update.txt', 'file'));

        $this->assertSame('file', $this->adapter->read('encrypt_update.txt'));
    }

    public function testPut()
    {
        $this->assertTrue($this->adapter->put('encrypt_put.txt', 'file'));
        $this->assertSame('file', $this->adapter->read('encrypt_put.txt'));

        $temp = tmpfile();
        fwrite($temp, 'dummy');
        rewind($temp);

        $this->assertTrue($this->adapter->put('encrypt_put.txt', $temp));
        $this->assertSame('dummy', $this->adapter->read('encrypt_put.txt'));

        $this->assertTrue($this->adapter->put('encrypt_put2.txt', $temp));
        $this->assertSame('dummy', $this->adapter->read('encrypt_put.txt'));
    }

    public function testUpdateStream()
    {
        $temp = tmpfile();
        fwrite($temp, 'dummy');
        rewind($temp);

        $this->assertTrue($this->adapter->updateStream('encrypt_u_stream.txt', $temp));
        $this->assertSame('dummy', $this->adapter->read('encrypt_u_stream.txt'));

        $temp = tmpfile();
        fwrite($temp, 'file');
        rewind($temp);

        $this->assertTrue($this->adapter->updateStream('encrypt_u_stream.txt', $temp));
        $this->assertSame('file', $this->adapter->read('encrypt_u_stream.txt'));
    }

    /**
     * @expectedException \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testRead()
    {
        $this->assertFalse($this->adapter->read('dont.txt'));
    }

    /**
     * @expectedException \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testReadStream()
    {
        $this->assertFalse($this->adapter->readStream('dont.txt'));
    }

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
