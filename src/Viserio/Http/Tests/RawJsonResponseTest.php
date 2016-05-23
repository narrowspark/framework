<?php
namespace Viserio\Test\Http\JsonResponse;

use Viserio\Http\JsonResponse\RawJsonResponse;

/**
 * RawJsonResponseTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8
 */
class RawJsonResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getRawJsonData
     */
    public function testConstructor($data)
    {
        $response = new RawJsonResponse($data);
        $this->assertEquals(RawJsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($data, $response->getContent());
    }

    /**
     * @dataProvider getRawJsonData
     */
    public function testConstructorWithStatusCode($data)
    {
        $response = new RawJsonResponse($data, RawJsonResponse::HTTP_FORBIDDEN);
        $this->assertEquals(RawJsonResponse::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals($data, $response->getContent());
    }

    /**
     * @dataProvider getRawJsonData
     */
    public function testSetData($data)
    {
        $response = new RawJsonResponse();
        $response->setData($data);
        $this->assertEquals($data, $response->getContent());
    }

    /**
     * @return array
     */
    public function getRawJsonData(): array
    {
        $data = [
            'property' => 1,
            'hello' => 'something',
            'object' => new \stdClass(),
        ];

        return [
            [json_encode($data)],
        ];
    }
}
