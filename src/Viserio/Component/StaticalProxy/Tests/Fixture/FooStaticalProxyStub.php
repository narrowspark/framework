<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests\Fixture;

use Viserio\Component\StaticalProxy\StaticalProxy;

class FooStaticalProxyStub extends StaticalProxy
{
    /**
     * {@inheritdoc}
     */
    public static function getInstanceIdentifier()
    {
        return 'foo';
    }
}
