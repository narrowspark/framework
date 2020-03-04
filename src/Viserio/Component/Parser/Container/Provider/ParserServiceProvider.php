<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Parser\Container\Provider;

use Viserio\Component\Parser\Dumper;
use Viserio\Component\Parser\FileLoader;
use Viserio\Component\Parser\GroupParser;
use Viserio\Component\Parser\Parser;
use Viserio\Component\Parser\TaggableParser;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Parser\Loader as LoaderContract;

class ParserServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(LoaderContract::class, FileLoader::class);
        $container->singleton(TaggableParser::class, TaggableParser::class);
        $container->singleton(GroupParser::class, GroupParser::class);
        $container->singleton(Parser::class, Parser::class);
        $container->singleton(Dumper::class, Dumper::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            FileLoader::class => LoaderContract::class,
            'parser' => Parser::class,
        ];
    }
}
