<?php
declare(strict_types=1);
namespace Viserio\Component\Hashing\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Hashing\Password as PasswordContract;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Hashing\Password;

class HashingServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.hashing';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            PasswordContract::class => [self::class, 'createPassword'],
            Password::class         => function (ContainerInterface $container) {
                return $container->get(PasswordContract::class);
            },
            'password' => function (ContainerInterface $container) {
                return $container->get(PasswordContract::class);
            },
        ];
    }

    public static function createPassword(ContainerInterface $container): Password
    {
        return new Password(self::getConfig($container, 'key', ''));
    }
}
