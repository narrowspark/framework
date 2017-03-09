<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Proxies;

use Viserio\Bridge\Doctrine\ORM\DoctrineManager;
use Viserio\Component\StaticalProxy\StaticalProxy;

class Doctrine extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return DoctrineManager::class;
    }
}