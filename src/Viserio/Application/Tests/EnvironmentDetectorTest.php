<?php
namespace Viserio\Application\Test;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Viserio\Application\EnvironmentDetector;
use Mockery as Mock;

/**
 * EnvironmentDetectorTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class EnvironmentDetectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Application\EnvironmentDetector
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
        $result = $this->env->detect(function () { return 'foobar'; });
        $this->assertEquals('foobar', $result);
    }

    public function testConsoleEnvironmentDetection()
    {
        $result = $this->env->detect(function () { return 'foobar'; }, ['--env=local']);
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
