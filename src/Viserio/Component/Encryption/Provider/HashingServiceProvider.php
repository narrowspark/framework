<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Encryption\Password as PasswordContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Encryption\Password;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class HashingServiceProvider implements
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
        return ['viserio', 'hashing'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['key'];
    }

    /**
     * Create a new Password instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Hashing\Password
     */
    public static function createPassword(ContainerInterface $container): Password
    {
        $options = self::resolveOptions($container);

        return new Password($options['key']);
    }
}
