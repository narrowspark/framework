<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Encryption;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Filesystem\Encryption\File;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FileTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Viserio\Component\Filesystem\Encryption\File
     */
    private $file;

    /**
     * @var \org\bovigo\vfs\vfsStream
     */
    private $root;

    public function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup();

        $password = \random_bytes(32);
        $key = KeyFactory::generateKey($password);

        $this->file = new File($key);
    }

    public function testEncrypt()
    {
        $file = vfsStream::newFile('file.txt')->at($this->root);
        $encryptedFile = vfsStream::newFile('encrypted.txt')->at($this->root);
        $decryptedFile = vfsStream::newFile('decrypted.txt')->at($this->root);

        $this->file->encrypt($file->url(), $encryptedFile->url());
        $this->file->decrypt($encryptedFile->url(), $decryptedFile->url());

        self::assertSame(
            \hash_file('sha256', $file->url()),
            \hash_file('sha256', $decryptedFile->url())
        );
    }
}