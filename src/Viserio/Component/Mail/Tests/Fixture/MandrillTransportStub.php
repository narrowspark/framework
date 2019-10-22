<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Mail\Tests\Fixture;

use Viserio\Component\Mail\Transport\MandrillTransport;

class MandrillTransportStub extends MandrillTransport
{
    protected $client;

    public function setHttpClient($client): void
    {
        $this->client = $client;
    }

    protected function getHttpClient()
    {
        return $this->client;
    }
}
