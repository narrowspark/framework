<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Proxy;

use Viserio\Component\Console\Application;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Console extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return Application::class;
    }
}
