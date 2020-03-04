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

namespace Viserio\Component\Console\Tests\Traits;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Output\SpyOutput;
use Viserio\Component\Console\Tests\Fixture\ViserioConfirmableFalseCommand;
use Viserio\Component\Console\Tests\Fixture\ViserioConfirmableTrueCommand;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ConfirmableTraitTest extends TestCase
{
    /** @var \Viserio\Component\Console\Application */
    private $application;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $container = new ArrayContainer([
            'env' => 'prod',
        ]);

        $this->application = new Application('1.0.0');
        $this->application->setContainer($container);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['SHELL_VERBOSITY'], $_GET['SHELL_VERBOSITY'], $_SERVER['SHELL_VERBOSITY']);
    }

    public function testConfirmableCommandWithTrue(): void
    {
        $this->application->add(new ViserioConfirmableTrueCommand());

        self::assertOutputIs(
            'confirmable',
            '**********************************************
*     Application is in Production mode!     *
**********************************************

'
        );
    }

    public function testConfirmableCommandWithFalse(): void
    {
        $this->application->add(new ViserioConfirmableFalseCommand());

        self::assertOutputIs(
            'confirmable',
            '**********************************************
*     Application is in Production mode!     *
**********************************************

Command Cancelled!
'
        );
    }

    public function testConfirmableCommandWithFalseAndForce(): void
    {
        $this->application->add(new ViserioConfirmableFalseCommand());

        self::assertOutputIs(
            'confirmable --force',
            null
        );
    }

    /**
     * @param null|string $expected
     */
    private function assertOutputIs(string $command, $expected): void
    {
        $output = new SpyOutput();

        $this->application->run(new StringInput($command), $output);

        self::assertEquals($expected, $output->output);
    }
}
