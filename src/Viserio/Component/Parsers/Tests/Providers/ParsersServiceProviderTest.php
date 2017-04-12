<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Parsers\Providers\ParsersServiceProvider;
use Viserio\Component\Parsers\TaggableParser;

class ParsersServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ParsersServiceProvider());

        self::assertInstanceOf(FileLoader::class, $container->get(FileLoader::class));
        self::assertInstanceOf(FileLoader::class, $container->get(LoaderContract::class));
        self::assertInstanceOf(TaggableParser::class, $container->get(TaggableParser::class));
        self::assertInstanceOf(TaggableParser::class, $container->get('parser'));
    }
}
