<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Session\Handler\CookieSessionHandler;
use Viserio\Component\Session\SessionManager;

class SessionServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            SessionManager::class       => [self::class, 'createSessionManager'],
            'session'                   => function (ContainerInterface $container) {
                return $container->get(SessionManager::class);
            },
            'session.store'             => [self::class, 'createSessionStore'],
            EventManagerContract::class => [self::class, 'extendEventManager'],
        ];
    }

    /**
     * Extend viserio events with data collector.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\Events\EventManager
     */
    public static function extendEventManager(ContainerInterface $container, ?callable $getPrevious = null): ?EventManagerContract
    {
        $eventManager = $getPrevious();

        if ($eventManager !== null) {
            $eventManager->attach(TerminableContract::TERMINATE, function (EventContract $event) {
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
        return new SessionManager($container);
    }

    /**
     * Create session store from default driver.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contracts\Session\Store
     */
    public static function createSessionStore(ContainerInterface $container): StoreContract
    {
        return $container->get(SessionManager::class)->getDriver();
    }
}
