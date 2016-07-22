<?php
declare(strict_types=1);
namespace Viserio\Mail\Tests\Fixture;

use Viserio\Mail\Transport\Postmark;

class PostmarkTransportStub extends Postmark
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
