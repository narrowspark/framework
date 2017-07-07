<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cookie\CookieJar;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class CookieServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            JarContract::class => [self::class, 'createCookieJar'],
            'cookie'           => function (ContainerInterface $container) {
                return $container->get(JarContract::class);
            },
            CookieJar::class => function (ContainerInterface $container) {
                return $container->get(JarContract::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'cookie'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['path', 'domain'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'secure' => true,
        ];
    }

    /**
     * Create a new CookieJar instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contracts\Cookie\QueueingFactory
     */
    public static function createCookieJar(ContainerInterface $container): CookieJar
    {
        $options = self::resolveOptions($container);

        return (new CookieJar())->setDefaultPathAndDomain(
            $options['path'],
            $options['domain'],
            $options['secure']
        );
    }
}
