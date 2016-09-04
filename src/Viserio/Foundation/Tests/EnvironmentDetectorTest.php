<?php
declare(strict_types=1);
namespace Viserio\Foundation\Tests;

use Mockery as Mock;
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

    public function tearDown()
    {
        Mock::close();
    }

    public function testClosureCanBeUsedForCustomEnvironmentDetection()
    {
        $result = $this->env->detect(function () {
            return 'foobar';
        });
        $this->assertEquals('foobar', $result);
    }

    public function testConsoleEnvironmentDetection()
    {
        $result = $this->env->detect(function () {
            return 'foobar';
        }, ['--env=local']);
        $this->assertEquals('local', $result);
    }

    public function testAbilityToCollectCodeCoverageCanBeAssessed()
    {
        $this->assertInternalType('boolean', $this->env->canCollectCodeCoverage());
    }

    public function testCanBeDetected()
    {
        $this->assertInternalType('boolean', $this->env->isHHVM());
    }

    public function testCanBeDetected2()
    {
        $this->assertInternalType('boolean', $this->env->isPHP());
    }

    public function testXdebugCanBeDetected()
    {
        $this->assertInternalType('boolean', $this->env->hasXdebug());
    }

    public function testVersionCanBeRetrieved()
    {
        $this->assertInternalType('string', $this->env->getVersion());
    }

    public function testIsRunningInConsole()
    {
        $this->assertInternalType('boolean', $this->env->runningInConsole());
    }
}
