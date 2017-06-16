<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Parsers\Dumper;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Parsers\GroupParser;
use Viserio\Component\Parsers\Parser;
use Viserio\Component\Parsers\Provider\ParsersServiceProvider;
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
        self::assertInstanceOf(GroupParser::class, $container->get(GroupParser::class));
        self::assertInstanceOf(Parser::class, $container->get(Parser::class));
        self::assertInstanceOf(Dumper::class, $container->get(Dumper::class));
        self::assertInstanceOf(Parser::class, $container->get('parser'));
    }
}
