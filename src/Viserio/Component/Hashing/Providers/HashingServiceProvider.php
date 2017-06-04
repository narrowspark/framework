<?php
declare(strict_types=1);
namespace Viserio\Component\Hashing\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Hashing\Password as PasswordContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Hashing\Password;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class HashingServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use StaticOptionsResolverTrait;

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
    public function getDimensions(): iterable
    {
        return ['viserio', 'hashing'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
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

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
