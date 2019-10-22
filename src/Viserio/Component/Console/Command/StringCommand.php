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

namespace Viserio\Component\Console\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Viserio\Component\Console\Input\InputArgument;
use Viserio\Component\Console\Input\InputOption;
use Viserio\Contract\Console\Exception\InvalidArgumentException;

/**
 * Code in this class it taken from silly.
 *
 * See the original here: https://github.com/mnapoli/silly/blob/master/src/Command/Command.php
 *
 * @author Matthieu Napoli https://github.com/mnapoli
 * @copyright Copyright (c) Matthieu Napoli
 */
class StringCommand extends BaseCommand
{
    /**
     * Define descriptions for the command and it's arguments/options.
     *
     * @param string $description                   description of the command
     * @param array  $argumentAndOptionDescriptions descriptions of the arguments and options
     *
     * @return $this
     */
    public function descriptions(string $description, array $argumentAndOptionDescriptions = []): self
    {
        $definition = $this->getDefinition();

        $this->setDescription($description);

        foreach ($argumentAndOptionDescriptions as $name => $value) {
            if (\strpos($name, '--') === 0) {
                $argument = $definition->getOption(\substr($name, 2));

                if ($argument instanceof InputOption) {
                    $argument->setDescription($value);
                }
            } else {
                $argument = $definition->getArgument($name);

                if ($argument instanceof InputArgument) {
                    $argument->setDescription($value);
                }
            }
        }

        return $this;
    }

    /**
     * Define default values for the arguments of the command.
     *
     * @param array $defaults default argument values
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Viserio\Contract\Console\Exception\InvalidArgumentException
     *
     * @return $this
     */
    public function defaults(array $defaults = []): self
    {
        $definition = $this->getDefinition();

        foreach ($defaults as $name => $default) {
            if ($definition->hasArgument($name)) {
                $input = $definition->getArgument($name);
            } elseif ($definition->hasOption($name)) {
                $input = $definition->getOption($name);
            } else {
                throw new InvalidArgumentException(\sprintf('Unable to set default for [%s]. It does not exist as an argument or option.', $name));
            }

            $input->setDefault($default);
        }

        return $this;
    }
}
