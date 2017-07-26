<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Parsers\Dumper;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Parsers\GroupParser;
use Viserio\Component\Parsers\Parser;
use Viserio\Component\Parsers\TaggableParser;

class ParsersServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices(): array
    {
        return [
            LoaderContract::class => function (ContainerInterface $container): FileLoader {
                return new FileLoader();
            },
            FileLoader::class     => function (ContainerInterface $container) {
                return $container->get(LoaderContract::class);
            },
            TaggableParser::class => function (ContainerInterface $container): TaggableParser {
                return new TaggableParser();
            },
            GroupParser::class    => function (ContainerInterface $container): GroupParser {
                return new GroupParser();
            },
            Parser::class         => function (ContainerInterface $container): Parser {
                return new Parser();
            },
            'parser'              => function (ContainerInterface $container) {
                return $container->get(Parser::class);
            },
            Dumper::class         => function (ContainerInterface $container): Dumper {
                return new Dumper();
            },
        ];
    }
}
