<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Tests\Fixture;

class SanitizerFixture
{
    public function foo($data)
    {
        return \strrev($data);
    }
}
