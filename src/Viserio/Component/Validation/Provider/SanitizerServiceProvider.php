<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Provider;

use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Validation\Sanitizer;

class SanitizerServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            Sanitizer::class => [self::class, 'createSanitizer'],
            'sanitizer'      => function (ContainerInterface $container) {
                return $container->get(Sanitizer::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * Create a sanitizer instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Validation\Sanitizer
     */
    public static function createSanitizer(ContainerInterface $container): Sanitizer
    {
        $sanitizer = new Sanitizer();
        $sanitizer->setContainer($container);

        return $sanitizer;
    }
}
