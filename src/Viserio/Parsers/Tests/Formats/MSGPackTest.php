<?php
namespace Viserio\Parsers\Tests\Formats\Formats;

use Viserio\Parsers\Formats\MSGPack;

class MSGPackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Viserio\Parsers\Formats\MSGPack
     */
    private $parser;

    public function setUp()
    {
        if (!function_exists('msgpack_unpack')) {
            $this->markTestSkipped('Failed To Parse MSGPack - Supporting Library Not Available');
        }

        $this->parser = new MSGPack();
    }
}
