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

        $this->assertEquals('local', $result);

        $result = $this->env->detect(function () {
            return 'foobar';
        }, ['env=local']);

        $this->assertEquals('foobar', $result);
    }

    public function testConsoleEnvironmentDetection(): void
    {
        $result = $this->env->detect(function () {
            return 'foobar';
        });

        $this->assertEquals('foobar', $result);
    }

    public function testAbilityToCollectCodeCoverageCanBeAssessed(): void
    {
        $this->assertIsBool($this->env->canCollectCodeCoverage());
    }

    public function testCanBeDetected(): void
    {
        $this->assertIsBool($this->env->isPHP());
    }

    public function testXdebugCanBeDetected(): void
    {
        $this->assertIsBool($this->env->hasXdebug());
    }

    public function testVersionCanBeRetrieved(): void
    {
        $this->assertIsString($this->env->getVersion());
    }

    public function testIsRunningInConsole(): void
    {
        $this->assertIsBool($this->env->runningInConsole());
    }
}
