<?php
namespace Viserio\Http\Test;

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
 * @version     0.10.0
 */

use Viserio\Http\JsonResponse;

/**
 * HttpRequestTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class HttpJsonResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testSetAndRetrieveData()
    {
        $response = new JsonResponse(['foo' => 'bar']);
        $data = $response->getData();
        $this->assertInstanceOf('StdClass', $data);
        $this->assertEquals('bar', $data->foo);
    }

    public function testSetAndRetrieveOptions()
    {
        $response = new JsonResponse(['foo' => 'bar']);
        $response->setJsonOptions(JSON_PRETTY_PRINT);
        $this->assertSame(JSON_PRETTY_PRINT, $response->getJsonOptions());
    }

    public function testSetAndRetrieveStatusCode()
    {
        $response = new JsonResponse(['foo' => 'bar'], 404);
        $this->assertSame(404, $response->getStatusCode());

        $response = new JsonResponse(['foo' => 'bar']);
        $response->setStatusCode(404);
        $this->assertSame(404, $response->getStatusCode());
    }
}
