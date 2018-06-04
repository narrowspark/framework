<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Parser\Loader as LoaderContract;
use Viserio\Component\Parser\Dumper;
use Viserio\Component\Parser\FileLoader;
use Viserio\Component\Parser\GroupParser;
use Viserio\Component\Parser\Parser;
use Viserio\Component\Parser\Provider\ParserServiceProvider;
use Viserio\Component\Parser\TaggableParser;

/**
 * @internal
 */
final class ParsersServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new ParserServiceProvider());

        $this->assertInstanceOf(FileLoader::class, $container->get(FileLoader::class));
        $this->assertInstanceOf(FileLoader::class, $container->get(LoaderContract::class));
        $this->assertInstanceOf(TaggableParser::class, $container->get(TaggableParser::class));
        $this->assertInstanceOf(GroupParser::class, $container->get(GroupParser::class));
        $this->assertInstanceOf(Parser::class, $container->get(Parser::class));
        $this->assertInstanceOf(Dumper::class, $container->get(Dumper::class));
        $this->assertInstanceOf(Parser::class, $container->get('parser'));
    }
}
