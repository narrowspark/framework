<?php
namespace Viserio\Parsers\Tests\Formats\Formats;

use Viserio\Parsers\Formats\BSON;

class BSONTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Parsers\Formats\BSON
     */
    private $parser;

    public function setUp()
    {
        if (!function_exists('bson_decode')) {
            $this->markTestSkipped('Failed To Parse BSON - Supporting Library Not Available');
        }

        $this->parser = new BSON();
    }
}
