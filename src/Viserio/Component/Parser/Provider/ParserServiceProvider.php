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
            LoaderContract::class => static function (ContainerInterface $container): FileLoader {
                return new FileLoader();
            },
            FileLoader::class => static function (ContainerInterface $container) {
                return $container->get(LoaderContract::class);
            },
            TaggableParser::class => static function (ContainerInterface $container): TaggableParser {
                return new TaggableParser();
            },
            GroupParser::class => static function (ContainerInterface $container): GroupParser {
                return new GroupParser();
            },
            Parser::class => static function (ContainerInterface $container): Parser {
                return new Parser();
            },
            'parser' => static function (ContainerInterface $container) {
                return $container->get(Parser::class);
            },
            Dumper::class => static function (ContainerInterface $container): Dumper {
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
