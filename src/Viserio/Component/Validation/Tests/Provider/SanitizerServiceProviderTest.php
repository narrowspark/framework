<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Validation\Provider\SanitizerServiceProvider;
use Viserio\Component\Validation\Sanitizer;

/**
 * @internal
 */
final class SanitizerServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new SanitizerServiceProvider());

        $this->assertInstanceOf(Sanitizer::class, $container->get(Sanitizer::class));
        $this->assertInstanceOf(Sanitizer::class, $container->get('sanitizer'));
    }
}
