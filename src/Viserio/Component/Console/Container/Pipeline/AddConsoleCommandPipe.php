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

namespace Viserio\Component\Console\Container\Pipeline;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Viserio\Component\Console\CommandLoader\IteratorCommandLoader;
use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Pipe as PipeContract;

final class AddConsoleCommandPipe implements PipeContract
{
    /** @var string */
    public const TAG = 'console.command';

    /** @var string */
    private $commandLoaderServiceId;

    /** @var string */
    private $commandTag;

    /**
     * Create a new AddConsoleCommandPipe instance.
     *
     * @param string $commandLoaderServiceId
     * @param string $commandTag
     */
    public function __construct(
        string $commandLoaderServiceId = CommandLoaderInterface::class,
        string $commandTag = self::TAG
    ) {
        $this->commandLoaderServiceId = $commandLoaderServiceId;
        $this->commandTag = $commandTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $lazyCommandRefs = [];
        $serviceIds = [];

        foreach ($containerBuilder->getTagged($this->commandTag) as $definitionAndTags) {
            [$definition] = $definitionAndTags;

            if (! $definition instanceof ObjectDefinitionContract) {
                continue;
            }

            $id = $definition->getName();
            $class = $definition->getClass();

            if (! $r = $containerBuilder->getClassReflector($class)) {
                throw new InvalidArgumentException(\sprintf('Class [%s] used for service [%s] cannot be found.', $class, $id));
            }

            if (! $r->isSubclassOf(Command::class)) {
                throw new InvalidArgumentException(\sprintf('The service [%s] tagged [%s] must be a subclass of [%s].', $id, $this->commandTag, Command::class));
            }

            $commandName = $class::getDefaultName();

            if ($commandName === null) {
                if (! $definition->isPublic()) {
                    $commandId = 'console.command.public_alias.' . $id;

                    $containerBuilder->setAlias($id, $commandId)->setPublic(true);

                    $id = $commandId;
                }

                $serviceIds[] = $id;

                continue;
            }

            $lazyCommandRefs[$commandName] = (new ReferenceDefinition($id))
                ->setType($class);
            $definition->addMethodCall('setName', [$commandName]);
        }

        if (\count($lazyCommandRefs) !== 0) {
            $containerBuilder
                ->singleton($this->commandLoaderServiceId, IteratorCommandLoader::class)
                ->addArgument(new IteratorArgument($lazyCommandRefs))
                ->setPublic(true);

            $containerBuilder->setParameter('console.command.ids', $serviceIds);
        }
    }
}
