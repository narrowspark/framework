<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Providers;

use Defuse\Crypto\Key;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Encryption\Encrypter;

class EncrypterServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.encryption';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Encrypter::class         => [self::class, 'createEncrypter'],
            EncrypterContract::class => function (ContainerInterface $container) {
                return $container->get(Encrypter::class);
            },
            'encrypter' => function (ContainerInterface $container) {
                return $container->get(Encrypter::class);
            },
        ];
    }

    public static function createEncrypter(ContainerInterface $container): Encrypter
    {
        $encrypt = new Encrypter(
            Key::loadFromAsciiSafeString(self::getConfig($container, 'key', ''))
        );

        return $encrypt;
    }
}
