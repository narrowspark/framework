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

namespace Viserio\Component\Mail\Tests\Transport\Fixture;

class SendRawEmailMock
{
    protected $getResponse;

    public function __construct($responseValue)
    {
        $this->getResponse = $responseValue;
    }

    /**
     * Mock the get() call for the sendRawEmail response.
     */
    public function get($key): string
    {
        return $this->getResponse;
    }
}
