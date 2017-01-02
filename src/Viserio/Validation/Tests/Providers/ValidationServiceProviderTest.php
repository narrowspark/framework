<?php
declare(strict_types=1);
namespace Viserio\Validation\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Container\Container;
use Viserio\Contracts\Validation\Validator as ValidatorContract;
use Viserio\Validation\Providers\ValidationServiceProvider;
use Viserio\Validation\Validator;

class ValidationServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ValidationServiceProvider());

        self::assertInstanceOf(Validator::class, $container->get(Validator::class));
        self::assertInstanceOf(Validator::class, $container->get(ValidatorContract::class));
        self::assertInstanceOf(Validator::class, $container->get('validator'));
    }
}
