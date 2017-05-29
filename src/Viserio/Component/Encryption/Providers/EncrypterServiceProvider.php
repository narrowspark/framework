<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\OptionsResolver\OptionsResolver;

class EncrypterServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options = [];

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

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'encryption'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return ['key'];
    }

    public static function createEncrypter(ContainerInterface $container): Encrypter
    {
        self::resolveOptions($container);

        $encrypt = new Encrypter(self::$options['key']);

        return $encrypt;
    }

    /**
     * Resolve component options.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (count(self::$options) === 0) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
