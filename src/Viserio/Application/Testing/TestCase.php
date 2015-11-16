<?php
namespace Viserio\Application\Testing;

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

use Viserio\Application\Testing\Traits\ApplicationTrait;
use Viserio\Contracts\Application\Test as TestCaseContract;
use Viserio\Contracts\Application\Test as TestCaseContract;

/**
 * TestCase.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase implements TestCaseContract
{
    use ApplicationTrait;

    /**
     * When overriding this method, make sure you call parent::setUp().
     */
    public function setUp()
    {
        parent::setUp();

        if (!$this->app) {
            $this->refreshApplication();
        }

        $this->start();
    }

    /**
     * Run extra setup code.
     */
    protected function start()
    {
        // call more setup methods
    }

    /**
     * Run extra tear down code.
     */
    protected function finish()
    {
        // call more tear down methods
    }

    /**
     * When overriding this method, make sure you call parent::tearDown().
     */
    public function tearDown()
    {
        $this->finish();

        parent::tearDown();

        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        \Mockery::close();
    }

    /**
     * Assert that the element exists in the array.
     *
     * @param mixed  $needle
     * @param array  $haystack
     * @param string $msg
     */
    public static function assertInArray($needle, $haystack, $msg = '')
    {
        if ($msg === '') {
            $msg = sprintf('Expected the array to contain the element %s', $needle);
        }

        static::assertTrue(in_array($needle, $haystack, true), $msg);
    }

    /**
     * Assert that the specified method exists on the class.
     *
     * @param string $method
     * @param string $class
     * @param string $msg
     */
    public static function assertMethodExists($method, $class, $msg = '')
    {
        if ($msg === '') {
            $msg = sprintf('Expected the class %c to have method %s', $class, $needle);
        }

        static::assertTrue(method_exists($class, $method), $msg);
    }

    /**
     * Assert that the element exists in the json.
     *
     * @param string $needle
     * @param array  $haystack
     * @param string $msg
     *
     * @throws InvalidArgumentException
     */
    public static function assertInJson($needle, array $haystack, $msg = '')
    {
        if ($msg === '') {
            $msg = sprintf('Expected the array to contain the element %s', $needle);
        }

        $array = json_decode($needle, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(sprintf('Invalid json provided: %s', $needle));
        }

        static::assertArraySubset($haystack, $array, false, $msg);
    }
}
