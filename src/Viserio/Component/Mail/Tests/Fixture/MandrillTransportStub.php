<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
