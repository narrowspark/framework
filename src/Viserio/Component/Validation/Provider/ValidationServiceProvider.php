<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Provider;

use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\Translation\Translator as TranslatorContract;
use Viserio\Component\Contract\Validation\Validator as ValidatorContract;
use Viserio\Component\Validation\Validator;

class ValidationServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
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

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * Create a validator instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Validation\Validator
     */
    public static function createValidator(ContainerInterface $container): ValidatorContract
    {
        $validator = new Validator();

        /** @codeCoverageIgnoreStart */
        if ($container->has(TranslatorContract::class)) {
            $validator->setTranslator($container->get(TranslatorContract::class));
        }
        /** @codeCoverageIgnoreEnd */

        return $validator;
    }
}
