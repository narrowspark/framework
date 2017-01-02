<?php
declare(strict_types=1);
namespace Viserio\Validation\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Validation\Providers\SanitizerServiceProvider;
use Viserio\Validation\Sanitizer;
use PHPUnit\Framework\TestCase;

class SanitizerServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new SanitizerServiceProvider());

        self::assertInstanceOf(Sanitizer::class, $container->get(Sanitizer::class));
        self::assertInstanceOf(Sanitizer::class, $container->get('sanitizer'));
    }
}
