<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contracts\Encryption\Password as PasswordContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Encryption\Password;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class EncrypterServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

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
            PasswordContract::class => [self::class, 'createPassword'],
            Password::class         => function (ContainerInterface $container) {
                return $container->get(PasswordContract::class);
            },
            'password' => function (ContainerInterface $container) {
                return $container->get(PasswordContract::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'encryption'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['key'];
    }

    /**
     * Create a new Encrypter instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contracts\Encryption\Encrypter
     */
    public static function createEncrypter(ContainerInterface $container): EncrypterContract
    {
        $options = self::resolveOptions($container);

        return new Encrypter(KeyFactory::importFromHiddenString($options['key']));
    }

    /**
     * Create a new Password instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contracts\Encryption\Password
     */
    public static function createPassword(ContainerInterface $container): PasswordContract
    {
        return new Password($container->get(EncrypterContract::class));
    }
}
