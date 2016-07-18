<?php
namespace Viserio\Mail\Tests\Fixture;

use Viserio\Mail\Transport\Mandrill;

class MandrillTransportStub extends Mandrill
{
    protected $client;

    public function setHttpClient($client)
    {
        $this->client = $client;
    }

    protected function getHttpClient()
    {
        return $this->client;
    }
}
