<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Contracts\Parsers\TaggableParser as TaggableParserContract;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Parsers\TaggableParser;
use Viserio\Component\Parsers\GroupParser;
use Viserio\Component\Parsers\Parser;

class ParsersServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
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
        ];
    }
}
