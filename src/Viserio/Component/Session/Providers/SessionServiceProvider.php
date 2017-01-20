<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Encryption\Encrypter;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Session\SessionManager;

class SessionServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            SessionManager::class => [self::class, 'createSessionManager'],
            'session'             => function (ContainerInterface $container) {
                return $container->get(SessionManager::class);
            },
            'session.store' => [self::class, 'createSessionStore'],
        ];
    }

    public static function createSessionManager(ContainerInterface $container): SessionManager
    {
        return new SessionManager(
            $container,
            $container->get(Encrypter::class)
        );
    }

    public static function createSessionStore(ContainerInterface $container): StoreContract
    {
        return $container->get(SessionManager::class)->getDriver();
    }
}
