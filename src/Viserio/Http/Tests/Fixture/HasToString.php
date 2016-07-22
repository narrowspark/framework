<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Fixture;

class HasToString
{
    public function __toString()
    {
        return 'foo';
    }
}
