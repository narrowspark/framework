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

namespace Viserio\Component\Config\Command;

use ReflectionClass;
use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;

class ConfigReaderCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'option:read';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'option:read
        [class : Name of the class to reflect.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Reads the provided configuration file and displays config for the provided class name.';

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $className = $this->argument('class');
        $reflectionClass = new ReflectionClass($className);

        $configs = (new ConfigReader())->readConfig($reflectionClass);

        if (\count($configs) !== 0) {
            $interfaces = \array_flip($reflectionClass->getInterfaceNames());

            if (isset($interfaces[RequiresComponentConfigContract::class])) {
                $dimensions = $className::getDimensions();
                $configs = $configs[\end($dimensions)];
            }
        }

        $this->info("Output array:\n\n" . VarExporter::export($configs));

        return 0;
    }
}
