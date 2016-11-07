<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Encryption;

use Defuse\Crypto\Key;
use Defuse\Crypto\File;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;

class EncryptionWrapper
{
    /**
     * [__construct description]
     *
     * @param \Viserio\Contracts\Filesystem\Filesystem $adapter
     * @param \Defuse\Crypto\Key                       $key
     */
    public function __construct(FilesystemContract $adapter, Key $key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, $contents, array $config = []): bool
    {
        File::encryptFile($inputFilename, $outputFilename, $this->key)
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path): string
    {
        File::decryptFile($inputFilename, $outputFilename, $this->key)
    }
}
