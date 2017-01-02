<?php
declare(strict_types=1);
namespace Viserio\Parsers\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Container\Container;
use Viserio\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Parsers\FileLoader;
use Viserio\Parsers\Providers\ParsersServiceProvider;
use Viserio\Parsers\TaggableParser;

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
