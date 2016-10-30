<?php
declare(strict_types=1);
namespace Viserio\Validation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Validation\Sanitizer;

class SanitizerServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Sanitizer::class => [self::class, 'createSanitizer'],
            'sanitizer' => function (ContainerInterface $container) {
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
