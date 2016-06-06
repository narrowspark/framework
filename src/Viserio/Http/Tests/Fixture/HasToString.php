<?php
namespace Viserio\Http\Tests\Fixture;

class HasToString
{
    public function __toString()
    {
        return 'foo';
    }
}
