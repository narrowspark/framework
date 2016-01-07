<?php
namespace Viserio\Support\Tests\Fixture;

use Interop\Container\ContainerInterface;
/**
 * Simple container.
 */
class MockContainer implements ContainerInterface
{
    private $entries = array();

    public function __construct(array $entries = array())
    {
        $this->entries = $entries;
    }

    public function get($id)
    {
        return $this->entries[$id];
    }

    public function has($id)
    {
        return array_key_exists($id, $this->entries);
    }

    public function set($id, $value)
    {
        $this->entries[$id] = $value;
    }
}
