<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

use Viserio\Component\Contract\Container\TaggableServiceProvider as TaggableServiceProviderContract;

class SimpleTaggedServiceProvider implements TaggableServiceProviderContract
{
    public function getFactories(): array
    {
        return [
            'param' => [self::class, 'getParam'],
        ];
    }

    public function getExtensions()
    {
        return [];
    }

    public function getTags(): array
    {
        return [
            'test' => ['param'],
        ];
    }

    public static function getParam()
    {
        return 'value';
    }
}
