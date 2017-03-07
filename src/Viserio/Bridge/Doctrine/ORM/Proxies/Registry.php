<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Proxies;

use Viserio\Component\StaticalProxy\StaticalProxy;
use Doctrine\Common\Persistence\ManagerRegistry;

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
