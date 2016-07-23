<?php

declare(strict_types=1);
namespace Viserio\Http\Tests\Constraint;

use PHPUnit_Framework_Assert as Assert;

/**
 * Validate contain only the HTTP version number (e.g., "1.1", "1.0").
 */
class HttpProtocolVersion extends \PHPUnit_Framework_Constraint
{
    /**
     * @var string[]
     */
    protected $validHttpProtocolVersion = [
        '1.0',
        '1.1',
    ];

    /**
     * Asserts protocol version is valid.
     *
     * Protocol version MUST be:
     * - String type.
     * - Valid HTTP protocol version number.
     *
     * @param string $protocolVersion
     * @param string $message
     */
    public static function assertValid($protocolVersion, $message = '')
    {
        Assert::assertThat($protocolVersion, new self(), $message);
    }

    public function toString()
    {
        return 'is a valid HTTP protocol version number';
    }

    protected function matches($other)
    {
        if (! is_string($other)) {
            return false;
        }

        return in_array($other, $this->validHttpProtocolVersion, true);
    }
}
