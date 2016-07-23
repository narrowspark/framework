<?php

declare(strict_types=1);
namespace Viserio\StaticalProxy\Tests\Fixture;

use Viserio\StaticalProxy\StaticalProxy;

class FacadeStub extends StaticalProxy
{
    public static function getInstanceIdentifier()
    {
        return 'foo';
    }
}
