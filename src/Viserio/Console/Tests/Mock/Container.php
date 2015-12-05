<?php
namespace Viserio\Console\Tests\Mock;

use Interop\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private $entries = [];

    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    public function get($id)
    {
        return $this->entries[$id];
    }

    public function has($id)
    {
        return isset($this->entries[$id]);
    }
}
