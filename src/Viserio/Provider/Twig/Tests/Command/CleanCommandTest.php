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
