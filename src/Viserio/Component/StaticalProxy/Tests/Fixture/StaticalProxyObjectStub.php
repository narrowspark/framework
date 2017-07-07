<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests\Fixture;

use stdClass;
use Viserio\Component\StaticalProxy\StaticalProxy;

class StaticalProxyObjectStub extends StaticalProxy
{
    /**
     * {@inheritdoc}
     */
    public static function getInstanceIdentifier()
    {
        return new stdClass();
    }
}
