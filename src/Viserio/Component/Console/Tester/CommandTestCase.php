<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tester;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Console\Application;

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

    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @param array                                      $input
     * @param array                                      $options
     *
     * @return \Symfony\Component\Console\Tester\CommandTester
     */
    protected function executeCommand(Command $command, array $input = [], array $options = []): CommandTester
    {
        $this->application->add($command);

        $reflectionProperty = (new \ReflectionClass($command))->getProperty('defaultName');
        $reflectionProperty->setAccessible(true);

        $command = $this->application->find($reflectionProperty->getValue($command));

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()] + $input, $options);

        return $commandTester;
    }
}
