<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Component\Contracts\Validation\Validator as ValidatorContract;
use Viserio\Component\Validation\Validator;

class ValidationServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ValidatorContract::class => [self::class, 'createValidator'],
            Validator::class         => function (ContainerInterface $container) {
                return $container->get(ValidatorContract::class);
            },
            'validator' => function (ContainerInterface $container) {
                return $container->get(ValidatorContract::class);
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
