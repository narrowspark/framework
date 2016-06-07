<?php
namespace Viserio\Http\Uri\Traits;

use InvalidArgumentException;

trait PortValidateTrait
{
    /**
     * Supported Schemes.
     *
     * @var array
     */
    protected $allowedSchemes = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'sftp' => 22,
    ];

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @param string $scheme
     * @param int    $port
     *
     * @return bool
     */
    protected function isNonStandardPort($scheme, $port): bool
    {
        return ! isset($this->allowedSchemes[$scheme]) || $this->allowedSchemes[$scheme] !== $port;
    }

    /**
     * Validate a Port number
     *
     * @param mixed $port the port numberhast
     *
     * @throws InvalidArgumentException If the port number is invalid
     *
     * @return null|int
     */
    protected function validatePort($port)
    {
        if (is_bool($port)) {
            throw new InvalidArgumentException('The submitted port is invalid');
        }

        if (in_array($port, [null, ''])) {
            return null;
        }

        $port = (int) $port;

        if (1 > $port || 0xffff < $port) {
            throw new InvalidArgumentException(
                sprintf('Invalid port: %d. Must be between 1 and 65535', $port)
            );
        }

        return $port;
    }
}
