<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests\Fixture;

use Viserio\Component\StaticalProxy\StaticalProxy;

class ExceptionSaticalProxyStub extends StaticalProxy
{
    public static function getStaticalProxyRoot(): ?object
    {
        return null;
    }
}
