<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\OptionsResolver\Providers\OptionsResolverServiceProvider;

class OptionsResolverServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new OptionsResolverServiceProvider());

        self::assertInstanceOf(OptionsResolver::class, $container->get(OptionsResolver::class));
    }
}
