<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Encryption;

use Defuse\Crypto\Key;
use Defuse\Crypto\File;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;

class EncryptionWrapper
{
    /**
     * Encryption key.
     *
     * @var \Defuse\Crypto\Key
     */
    protected $key;

    /**
     * Filesystem instance.
     *
     * @var \Viserio\Contracts\Filesystem\Filesystem
     */
    protected $adapter;

    /**
     * [__construct description]
     *
     * @param \Viserio\Contracts\Filesystem\Filesystem $adapter
     * @param \Defuse\Crypto\Key                       $key
     */
    public function __construct(FilesystemContract $adapter, Key $key)
    {
        $this->adapter = $adapter;
        $this->key = $key;
    }

     /**
     * Call a Flysystem adapter plugin.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return call_user_func_array([$this->adapter, $method], $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, $contents, array $config = []): bool
    {
        File::encryptFile($inputFilename, $outputFilename, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path): string
    {
        File::decryptFile($inputFilename, $outputFilename, $this->key);
    }
}
