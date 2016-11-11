<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests\Transport\Fixture;

class SendRawEmailMock
{
    protected $getResponse = null;

    public function __construct($responseValue)
    {
        $this->getResponse = $responseValue;
    }

    /**
     * Mock the get() call for the sendRawEmail response
     *
     * @param string
     *
     * @return string
     */
    public function get($key)
    {
        return $this->getResponse;
    }
}
