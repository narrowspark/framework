<?php
declare(strict_types=1);
namespace Viserio\Foundation\Tests;

use Viserio\Foundation\EnvironmentDetector;

class EnvironmentDetectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Foundation\EnvironmentDetector
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
        self::assertInternalType('boolean', $this->env->isHHVM());
    }

    public function testCanBeDetected2()
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
