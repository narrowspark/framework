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

namespace Viserio\Component\Console\Tester;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Console\Application;

/**
 * @internal
 */
abstract class CommandTestCase extends TestCase
{
    /**
     * A Application instance.
     *
     * @var \Viserio\Component\Console\Application
     */
    protected $application;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();
    }

    protected function executeCommand(Command $command, array $input = [], array $options = []): CommandTester
    {
        $this->application->add($command);

        $reflectionProperty = (new ReflectionClass($command))->getProperty('defaultName');
        $reflectionProperty->setAccessible(true);

        $command = $this->application->find($reflectionProperty->getValue($command));

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()] + $input, $options);

        return $commandTester;
    }
}
