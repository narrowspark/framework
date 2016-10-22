<?php
declare(strict_types=1);
namespace Viserio\Validation\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Validation\Providers\SanitizerServiceProvider;
use Viserio\Validation\Sanitizer;

class SanitizerServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new SanitizerServiceProvider());

        $this->assertInstanceOf(Sanitizer::class, $container->get(Sanitizer::class));
        $this->assertInstanceOf(Sanitizer::class, $container->get('sanitizer'));
    }
}
