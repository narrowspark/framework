<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cookie\CookieJar;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class CookieServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    use StaticOptionsResolverTrait;

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
    public function getDimensions(): iterable
    {
        return ['viserio', 'cookie'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return ['path', 'domain'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'secure' => true,
        ];
    }

    public static function createCookieJar(ContainerInterface $container): CookieJar
    {
        $options = self::resolve($container);

        return (new CookieJar())->setDefaultPathAndDomain(
            $options['path'],
            $options['domain'],
            $options['secure']
        );
    }

    protected static function resolveConfiguration($data)
    {
        if (is_iterable($data)) {
            return $data;
        } elseif ($data instanceof ContainerInterface) {
            if ($data->has(RepositoryContract::class)) {
                return $data->get(RepositoryContract::class);
            } elseif ($data->has('config')) {
                return $data->get('config');
            } elseif ($data->has('options')) {
                return $data->get('options');
            }
        }
        throw new RuntimeException('No configuration found.');
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
