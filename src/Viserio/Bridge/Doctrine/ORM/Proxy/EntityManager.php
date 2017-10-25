<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Proxy;

use Doctrine\ORM\EntityManagerInterface;
use Viserio\Component\StaticalProxy\StaticalProxy;

class EntityManager extends StaticalProxy
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function getInstanceIdentifier()
    {
        return EntityManagerInterface::class;
    }
}
