<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Proxy;

use Doctrine\Common\Persistence\ManagerRegistry;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Registry extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return ManagerRegistry::class;
    }
}
