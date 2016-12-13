<?php
declare(strict_types=1);
namespace Viserio\Http\Uri\Traits;

use InvalidArgumentException;

trait PortValidateTrait
{
    /**
     * The default port per scheme.
     *
     * @var array
     */
    protected $allowedSchemes = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'sftp' => 22,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    /**
     * Is a given port non-standard for the current scheme?
     *
     * @param string   $scheme
     * @param int|null $port
     *
     * @return bool
     */
    protected function isNonStandardPort(string $scheme, ?int $port): bool
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
    protected function validatePort($port): ?int
    {
        if (is_bool($port)) {
            throw new InvalidArgumentException('The submitted port is invalid');
        }

        if ($port === null || $port === '') {
            return;
        }

        $res = filter_var($port, FILTER_VALIDATE_INT, ['options' => [
            'min_range' => 1,
            'max_range' => 65535,
        ]]);

        if (! $res) {
            throw new InvalidArgumentException(
                sprintf('Invalid port: %d. Must be between 1 and 65535', $port)
            );
        }

        return $res;
    }
}
