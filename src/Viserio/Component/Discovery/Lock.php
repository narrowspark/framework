<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery;

use Composer\Json\JsonFile;

class Lock
{
    /**
     * @var JsonFile
     */
    private $json;

    /**
     * @var array|mixed
     */
    private $lock = [];

    /**
     * Create a new Lock instance.
     *
     * @param string $lockFile
     */
    public function __construct(string $lockFile)
    {
        $this->json = new JsonFile($lockFile);

        if ($this->json->exists()) {
            $this->lock = $this->read();
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->lock);
    }

    /**
     * @param string $name
     * @param array  $data
     *
     * @return void
     */
    public function add(string $name, array $data): void
    {
        $this->lock[$name] = $data;
    }

    /**
     * @param string $name
     */
    public function remove(string $name): void
    {
        unset($this->lock[$name]);
    }

    /**
     * Write a lock file.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function write(): void
    {
        \ksort($this->lock);

        $this->json->write($this->lock);
    }

    /**
     * Read the lock file.
     *
     * @return array
     */
    public function read(): array
    {
        return $this->json->read() ?? [];
    }
}
