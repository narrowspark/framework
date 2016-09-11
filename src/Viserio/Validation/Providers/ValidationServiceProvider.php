<?php
declare(strict_types=1);
namespace Viserio\Validation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Validation\Validator;

class ValidationServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Validator::class => [self::class, 'createValidator'],
            'validator' => function (ContainerInterface $container) {
                return $container->get(Validator::class);
            },
        ];
    }

    public static function createValidator(ContainerInterface $container): Validator
    {
        return new Validator();
    }
}
