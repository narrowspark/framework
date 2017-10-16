<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Parser\Loader as LoaderContract;
use Viserio\Component\Parser\Dumper;
use Viserio\Component\Parser\FileLoader;
use Viserio\Component\Parser\GroupParser;
use Viserio\Component\Parser\Parser;
use Viserio\Component\Parser\TaggableParser;

class ParserServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            LoaderContract::class => function (ContainerInterface $container): FileLoader {
                return new FileLoader();
            },
            FileLoader::class => function (ContainerInterface $container) {
                return $container->get(LoaderContract::class);
            },
            TaggableParser::class => function (ContainerInterface $container): TaggableParser {
                return new TaggableParser();
            },
            GroupParser::class => function (ContainerInterface $container): GroupParser {
                return new GroupParser();
            },
            Parser::class => function (ContainerInterface $container): Parser {
                return new Parser();
            },
            'parser' => function (ContainerInterface $container) {
                return $container->get(Parser::class);
            },
            Dumper::class => function (ContainerInterface $container): Dumper {
                return new Dumper();
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }
}
