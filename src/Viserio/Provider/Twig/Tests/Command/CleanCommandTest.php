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

namespace Viserio\Provider\Twig\Tests\Command;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Support\Invoker;
use Viserio\Provider\Twig\Command\CleanCommand;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class CleanCommandTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Console\Command\AbstractCommand */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $path = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'test';

        \mkdir($path);

        $command = new CleanCommand(new Filesystem(), $path);
        $command->setInvoker(new Invoker());

        $this->command = $command;
    }

    public function testSuccess(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertStringContainsString('Twig cache cleaned.', $output);
    }
}
