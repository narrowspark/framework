<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Cache\Manager as CacheManagerContract;
use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contract\Events\Event as EventContract;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Session\Handler\CookieSessionHandler;
use Viserio\Component\Session\SessionManager;

class SessionServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            SessionManager::class => [self::class, 'createSessionManager'],
            'session'             => function (ContainerInterface $container) {
                return $container->get(SessionManager::class);
            },
            'session.store' => [self::class, 'createSessionStore'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            EventManagerContract::class => [self::class, 'extendEventManager'],
        ];
    }

    /**
     * Extend viserio events with data collector.
     *
     * @param \Psr\Container\ContainerInterface                    $container
     * @param null|\Viserio\Component\Contract\Events\EventManager $eventManager
     *
     * @return null|\Viserio\Component\Contract\Events\EventManager
     */
    public static function extendEventManager(
        ContainerInterface $container,
        ?EventManagerContract $eventManager = null
    ): ?EventManagerContract {
        if ($eventManager !== null) {
            $eventManager->attach(TerminableContract::TERMINATE, function (EventContract $event): void {
                /** @var StoreContract $driver */
                $driver = $event->getTarget()->getContainer()->get(SessionManager::class)->getDriver();

                if (! $driver->getHandler() instanceof CookieSessionHandler) {
                    $driver->save();
                }
            });
        }

        return $eventManager;
    }

    /**
     * Create new session manager instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Session\SessionManager
     */
    public static function createSessionManager(ContainerInterface $container): SessionManager
    {
        $manager = new SessionManager($container);

        if ($container->has(CacheManagerContract::class)) {
            $manager->setCacheManager($container->get(CacheManagerContract::class));
        }

        if ($container->has(JarContract::class)) {
            $manager->setCookieJar($container->get(JarContract::class));
        }

        return $manager;
    }

    /**
     * Create session store from default driver.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Session\Store
     */
    public static function createSessionStore(ContainerInterface $container): StoreContract
    {
        return $container->get(SessionManager::class)->getDriver();
    }
}
