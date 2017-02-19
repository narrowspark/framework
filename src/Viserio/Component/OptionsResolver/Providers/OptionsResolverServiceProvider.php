<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Providers;

use Interop\Container\ServiceProvider;
use Viserio\Component\OptionsResolver\OptionsResolver;

class OptionsResolverServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            OptionsResolver::class => [self::class, 'createOptionsResolver'],
        ];
    }

    public static function createOptionsResolver(): OptionsResolver
    {
        return new OptionsResolver();
    }
}
