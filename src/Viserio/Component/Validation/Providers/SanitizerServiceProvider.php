<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Providers;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Validation\Sanitizer;

class SanitizerServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Sanitizer::class => [self::class, 'createSanitizer'],
            'sanitizer'      => function (ContainerInterface $container) {
                return $container->get(Sanitizer::class);
            },
        ];
    }

    public static function createSanitizer(ContainerInterface $container): Sanitizer
    {
        $sanitizer = new Sanitizer();
        $sanitizer->setContainer($container);

        return $sanitizer;
    }
}
