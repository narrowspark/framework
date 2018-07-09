<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Foundation\EnvironmentDetector;

/**
 * @internal
 */
final class EnvironmentDetectorTest extends TestCase
{
    /**
     * @var \Viserio\Component\Foundation\EnvironmentDetector
     */
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
        $result = $this->env->detect(function () {
            return 'foobar';
        }, ['--env=local']);

        static::assertEquals('local', $result);

        $result = $this->env->detect(function () {
            return 'foobar';
        }, ['env=local']);

        static::assertEquals('foobar', $result);
    }

    public function testConsoleEnvironmentDetection(): void
    {
        $result = $this->env->detect(function () {
            return 'foobar';
        });

        static::assertEquals('foobar', $result);
    }

    public function testAbilityToCollectCodeCoverageCanBeAssessed(): void
    {
        static::assertInternalType('boolean', $this->env->canCollectCodeCoverage());
    }

    public function testCanBeDetected(): void
    {
        static::assertInternalType('boolean', $this->env->isPHP());
    }

    public function testXdebugCanBeDetected(): void
    {
        static::assertInternalType('boolean', $this->env->hasXdebug());
    }

    public function testVersionCanBeRetrieved(): void
    {
        static::assertInternalType('string', $this->env->getVersion());
    }

    public function testIsRunningInConsole(): void
    {
        static::assertInternalType('boolean', $this->env->runningInConsole());
    }
}
