<?php
namespace Viserio\Http\Uri\Filter;

use Viserio\Http\Uri\Traits\PortValidateTrait;

class Port
{
    use PortValidateTrait;

    public function filter(string $scheme, $port = null)
    {
        if ($port === null) {
            return null;
        }

        $port = $this->validatePort($port);

        return $this->isNonStandardPort($scheme, $port) ? (int) $port : null;
    }
}
