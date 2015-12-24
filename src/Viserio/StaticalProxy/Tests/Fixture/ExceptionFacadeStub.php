<?php
namespace Viserio\StaticalProxy\Tests\Fixture;

use Viserio\StaticalProxy\StaticalProxy;

class ExceptionFacadeStub extends StaticalProxy
{
    public static function getStaticalProxyRoot()
    {
        return '';
    }
}
