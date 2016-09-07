<?php
declare(strict_types=1);
namespace Viserio\Session\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Encryption\Encrypter;
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
        return new SessionManager(
            $container->get(ConfigManager::class),
            $container->get(Encrypter::class),
            $container
        );
    }

    public static function createSessionStore(ContainerInterface $container): SessionManager
    {
        return $container->get(SessionManager::class)->getDriver();
    }
}
