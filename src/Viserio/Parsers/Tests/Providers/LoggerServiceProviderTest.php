<?php
declare(strict_types=1);
namespace Viserio\Parsers\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Parsers\FileLoader;
use Viserio\Parsers\Providers\ParsersServiceProvider;
use Viserio\Parsers\TaggableParser;

class LoggerServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ParsersServiceProvider());

        $this->assertInstanceOf(FileLoader::class, $container->get(FileLoader::class));
        $this->assertInstanceOf(FileLoader::class, $container->get(LoaderContract::class));
        $this->assertInstanceOf(TaggableParser::class, $container->get(TaggableParser::class));
        $this->assertInstanceOf(TaggableParser::class, $container->get('parser'));
    }
}
