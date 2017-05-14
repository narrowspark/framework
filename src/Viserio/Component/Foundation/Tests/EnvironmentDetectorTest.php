<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Foundation\EnvironmentDetector;

class EnvironmentDetectorTest extends TestCase
{
    /**
     * @var \Viserio\Component\Foundation\EnvironmentDetector
     */
    private $env;

    protected function setUp()
    {
        $this->env = new EnvironmentDetector();
    }

    public function testClosureCanBeUsedForCustomEnvironmentDetection()
    {
        $result = $this->env->detect(function () {
            return 'foobar';
        });

        self::assertEquals('foobar', $result);
    }

    public function testConsoleEnvironmentDetection()
    {
        $result = $this->env->detect(function () {
            return 'foobar';
        }, ['--env=local']);

        self::assertEquals('local', $result);
    }

    public function testAbilityToCollectCodeCoverageCanBeAssessed()
    {
        self::assertInternalType('boolean', $this->env->canCollectCodeCoverage());
    }

    public function testCanBeDetected()
    {
        self::assertInternalType('boolean', $this->env->isPHP());
    }

    public function testXdebugCanBeDetected()
    {
        self::assertInternalType('boolean', $this->env->hasXdebug());
    }

    public function testVersionCanBeRetrieved()
    {
        self::assertInternalType('string', $this->env->getVersion());
    }

    public function testIsRunningInConsole()
    {
        self::assertInternalType('boolean', $this->env->runningInConsole());
    }
}
