<?php
declare(strict_types=1);
namespace Viserio\Validation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Contracts\Validation\Validator as ValidatorContract;
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
            ValidatorContract::class => function (ContainerInterface $container) {
                return $container->get(Validator::class);
            },
            'validator' => function (ContainerInterface $container) {
                return $container->get(Validator::class);
            },
        ];
    }

    public static function createValidator(ContainerInterface $container): Validator
    {
        $validator = new Validator();

        // @codeCoverageIgnoreStart
        if ($container->has(TranslatorContract::class)) {
            $validator->setTranslator($container->get(TranslatorContract::class));
        }
        // @codeCoverageIgnoreEnd

        return $validator;
    }
}
