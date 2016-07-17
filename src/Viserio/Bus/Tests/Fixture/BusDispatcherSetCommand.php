<?php
namespace Viserio\Bus\Tests\Fixture;

class BusDispatcherSetCommand
{
    private $value = 'bar';

    public function set($value = '')
    {
        $this->value = $value;

        return $this;
    }

    public function handle()
    {
        return $this->value;
    }
}
