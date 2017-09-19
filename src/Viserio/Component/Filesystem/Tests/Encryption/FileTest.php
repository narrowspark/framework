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
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        parent::setUp();

        $this->root = vfsStream::setup();

        $password = \random_bytes(32);
        $key      = KeyFactory::generateKey($password);

        $this->file = new File($key);
    }

    public function testFileToFileEncryptionAndDecryption()
    {
        [$file, $encryptedFile, $decryptedFile] = $this->arrangeStreamFiles();

        $this->file->encrypt($file->url(), $encryptedFile->url());
        $this->file->decrypt($encryptedFile->url(), $decryptedFile->url());

        self::assertSame(
            \hash_file('sha256', $file->url()),
            \hash_file('sha256', $decryptedFile->url())
        );
    }

    public function testResourceToResourceEncryptionAndDecryption()
    {
        [$file, $encryptedFile, $decryptedFile] = $this->arrangeStreamFiles();

        $encryptedFileResource = fopen($encryptedFile->url(), 'r+b');
        $decryptedFileResource = fopen($decryptedFile->url(), 'wb');

        $this->file->encrypt($file->url(), $encryptedFileResource);
        $this->file->decrypt($encryptedFileResource, $decryptedFileResource);

        self::assertSame(
            \hash_file('sha256', $file->url()),
            \hash_file('sha256', $decryptedFile->url())
        );
    }

    public function testEmptyFileToFileEncryptionAndDecryption()
    {
        $file          = vfsStream::newFile('file.txt')->withContent('')->at($this->root);
        $encryptedFile = vfsStream::newFile('encrypted.txt')->at($this->root);
        $decryptedFile = vfsStream::newFile('decrypted.txt')->at($this->root);

        $this->file->encrypt($file->url(), $encryptedFile->url());
        $this->file->decrypt($encryptedFile->url(), $decryptedFile->url());

        self::assertSame(
            \hash_file('sha256', $file->url()),
            \hash_file('sha256', $decryptedFile->url())
        );
    }

    /**
     * @return array
     */
    private function arrangeStreamFiles(): array
    {
        $file          = vfsStream::newFile('file.txt')->withContent('test')->at($this->root);
        $encryptedFile = vfsStream::newFile('encrypted.txt')->at($this->root);
        $decryptedFile = vfsStream::newFile('decrypted.txt')->at($this->root);

        return [$file, $encryptedFile, $decryptedFile];
    }
}
