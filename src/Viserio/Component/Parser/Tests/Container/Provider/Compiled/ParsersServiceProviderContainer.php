<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Parser\Tests\Container\Provider\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class ParsersServiceProviderContainer extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->methodMapping = [
            \Viserio\Contract\Parser\Loader::class => 'get4646b0391e1ce02b23243f2fcf94fb8ec2b8b3b9c7b849566f86ddb2f0724fcd',
            \Viserio\Component\Parser\Dumper::class => 'get6344d2ab22689059abefa874afb9b38e9a85eb79ec642b197ba4cf86123dbea1',
            \Viserio\Component\Parser\GroupParser::class => 'getb395e16f6c98204c09eec44978726308a3c59138ae760a6fa39a2fca6127e823',
            \Viserio\Component\Parser\Parser::class => 'get919883ff517b9cfb3d69a7cfef7aca625f4f6d700fb5ce0d218d7f7ca981023e',
            \Viserio\Component\Parser\TaggableParser::class => 'get73f349ed998033f0cf51827574e0718ddf857ea577ecaace91ccba30117cfbf8',
        ];
        $this->aliases = [
            \Viserio\Component\Parser\FileLoader::class => \Viserio\Contract\Parser\Loader::class,
            'parser' => \Viserio\Component\Parser\Parser::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedIds(): array
    {
        return [
            \Psr\Container\ContainerInterface::class => true,
            \Viserio\Contract\Container\Factory::class => true,
            \Viserio\Contract\Container\TaggedContainer::class => true,
            'container' => true,
        ];
    }

    /**
     * Returns the public Viserio\Contract\Parser\Loader shared service.
     *
     * @return \Viserio\Component\Parser\FileLoader
     */
    protected function get4646b0391e1ce02b23243f2fcf94fb8ec2b8b3b9c7b849566f86ddb2f0724fcd(): \Viserio\Component\Parser\FileLoader
    {
        return $this->services[\Viserio\Contract\Parser\Loader::class] = new \Viserio\Component\Parser\FileLoader();
    }

    /**
     * Returns the public Viserio\Component\Parser\Dumper shared service.
     *
     * @return \Viserio\Component\Parser\Dumper
     */
    protected function get6344d2ab22689059abefa874afb9b38e9a85eb79ec642b197ba4cf86123dbea1(): \Viserio\Component\Parser\Dumper
    {
        return $this->services[\Viserio\Component\Parser\Dumper::class] = new \Viserio\Component\Parser\Dumper();
    }

    /**
     * Returns the public Viserio\Component\Parser\GroupParser shared service.
     *
     * @return \Viserio\Component\Parser\GroupParser
     */
    protected function getb395e16f6c98204c09eec44978726308a3c59138ae760a6fa39a2fca6127e823(): \Viserio\Component\Parser\GroupParser
    {
        return $this->services[\Viserio\Component\Parser\GroupParser::class] = new \Viserio\Component\Parser\GroupParser();
    }

    /**
     * Returns the public Viserio\Component\Parser\Parser shared service.
     *
     * @return \Viserio\Component\Parser\Parser
     */
    protected function get919883ff517b9cfb3d69a7cfef7aca625f4f6d700fb5ce0d218d7f7ca981023e(): \Viserio\Component\Parser\Parser
    {
        return $this->services[\Viserio\Component\Parser\Parser::class] = new \Viserio\Component\Parser\Parser();
    }

    /**
     * Returns the public Viserio\Component\Parser\TaggableParser shared service.
     *
     * @return \Viserio\Component\Parser\TaggableParser
     */
    protected function get73f349ed998033f0cf51827574e0718ddf857ea577ecaace91ccba30117cfbf8(): \Viserio\Component\Parser\TaggableParser
    {
        return $this->services[\Viserio\Component\Parser\TaggableParser::class] = new \Viserio\Component\Parser\TaggableParser();
    }
}
