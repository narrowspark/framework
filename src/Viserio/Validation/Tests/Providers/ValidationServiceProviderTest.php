<?php
declare(strict_types=1);
namespace Viserio\Validation\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Contracts\Validation\Validator as ValidatorContract;
use Viserio\Validation\Providers\ValidationServiceProvider;
use Viserio\Validation\Validator;

class ValidationServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ValidationServiceProvider());

        $this->assertInstanceOf(Validator::class, $container->get(Validator::class));
        $this->assertInstanceOf(Validator::class, $container->get(ValidatorContract::class));
        $this->assertInstanceOf(Validator::class, $container->get('validator'));
    }
}
