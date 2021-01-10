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

namespace Viserio\Component\Foundation\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Foundation\EnvironmentDetector;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class EnvironmentDetectorTest extends TestCase
{
    /** @var \Viserio\Component\Foundation\EnvironmentDetector */
    private $env;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->env = new EnvironmentDetector();
    }

    public function testClosureCanBeUsedForCustomEnvironmentDetection(): void
    {
        $result = $this->env->detect(static function () {
            return 'foobar';
        }, ['--env=local']);

        self::assertEquals('local', $result);

        $result = $this->env->detect(static function () {
            return 'foobar';
        }, ['env=local']);

        self::assertEquals('foobar', $result);
    }

    public function testConsoleEnvironmentDetection(): void
    {
        $result = $this->env->detect(static function () {
            return 'foobar';
        });

        self::assertEquals('foobar', $result);
    }

    public function testAbilityToCollectCodeCoverageCanBeAssessed(): void
    {
        self::assertIsBool($this->env->canCollectCodeCoverage());
    }

    public function testXdebugCanBeDetected(): void
    {
        self::assertIsBool($this->env->hasXdebug());
    }

    public function testVersionCanBeRetrieved(): void
    {
        self::assertIsString($this->env->getVersion());
    }

    public function testIsRunningInConsole(): void
    {
        self::assertIsBool($this->env->isRunningInConsole());
    }
}
