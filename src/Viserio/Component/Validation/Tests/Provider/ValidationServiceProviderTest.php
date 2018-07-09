<?php
declare(strict_types=1);
namespace Viserio\Component\Validation\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Validation\Validator as ValidatorContract;
use Viserio\Component\Validation\Provider\ValidationServiceProvider;
use Viserio\Component\Validation\Validator;

/**
 * @internal
 */
final class ValidationServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new ValidationServiceProvider());

        static::assertInstanceOf(Validator::class, $container->get(Validator::class));
        static::assertInstanceOf(Validator::class, $container->get(ValidatorContract::class));
        static::assertInstanceOf(Validator::class, $container->get('validator'));
    }
}
