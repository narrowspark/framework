<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\Fixture;

class DummyToString
{
    public function __toString()
    {
        return '';
    }
}
