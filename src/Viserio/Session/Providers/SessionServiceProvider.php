<?php
declare(strict_types=1);
namespace Viserio\Session\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Encryption\Encrypter;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Session\SessionManager;

class SessionServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            SessionManager::class => [self::class, 'createSessionManager'],
            'session' => function (ContainerInterface $container) {
                return $container->get(SessionManager::class);
            },
            'session.store' => [self::class, 'createSessionStore'],
        ];
    }

    public static function createSessionManager(ContainerInterface $container): SessionManager
    {
        $manager = new SessionManager(
            $container->get(RepositoryContract::class),
            $container->get(Encrypter::class)
        );

        $manager->setContainer($container);

        return $manager;
    }

    public static function createSessionStore(ContainerInterface $container): StoreContract
    {
        return $container->get(SessionManager::class)->driver();
    }
}
