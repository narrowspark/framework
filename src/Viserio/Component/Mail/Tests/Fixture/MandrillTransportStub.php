<?php
declare(strict_types=1);
namespace Viserio\Component\Mail\Tests\Fixture;

use Viserio\Component\Mail\Transport\MandrillTransport;

class MandrillTransportStub extends MandrillTransport
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
