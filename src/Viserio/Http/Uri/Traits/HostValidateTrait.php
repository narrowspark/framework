<?php

declare(strict_types=1);
namespace Viserio\Http\Uri\Traits;

use InvalidArgumentException;

trait HostValidateTrait
{
    /**
     * Tells whether we have a IDN or not
     *
     * @var bool
     */
    protected $isIdn = false;

    /**
     * Is the Host an IPv4
     *
     * @var bool
     */
    protected $hostAsIpv4 = false;

    /**
     * Is the Host an IPv6
     *
     * @var bool
     */
    protected $hostAsIpv6 = false;

    /**
     * Tell whether the IP has a zone Identifier
     *
     * @var bool
     */
    protected $hasZoneIdentifier = false;

    /**
     * IPv6 Local Link binary-like prefix
     *
     * @var string
     */
    protected static $localLinkPrefix = '1111111010';

    /**
     * validate the host component
     *
     * @param string $host
     *
     * @return string
     */
    protected function validateHost(string $host): string
    {
        if (empty($this->validateIpHost($host))) {
            $this->validateStringHost($host);
        }

        return $host;
    }

    /**
     * Validate a string only host
     *
     * @param string $str
     *
     * @return array
     */
    protected function validateStringHost(string $str): array
    {
        if ($str === '') {
            return [];
        }

        $host = $this->lower($this->setIsAbsolute($str));

        $rawLabels = explode('.', $host);

        $labels = array_map(function ($value) {
            return idn_to_ascii($value);
        }, $rawLabels);

        $this->assertValidHost($labels);
        $this->isIdn = $rawLabels !== $labels;

        return array_reverse(array_map(function ($label) {
            return idn_to_utf8($label);
        }, $labels));
    }

    /**
     * @param string $host
     *
     * @return string
     */
    protected function setIsAbsolute(string $host): string
    {
        return (mb_substr($host, -1, 1, 'UTF-8') == '.') ? mb_substr($host, 0, -1, 'UTF-8') : $host;
    }

    /**
     * {@inheritdoc}
     */
    protected function assertLabelsCount(array $labels)
    {
        if (127 <= count($labels)) {
            throw new InvalidArgumentException('Invalid Host, verify labels count');
        }
    }

    /**
     * Convert to lowercase a string without modifying unicode characters
     *
     * @param string $str
     *
     * @return string
     */
    protected function lower(string $str): string
    {
        return preg_replace_callback('/[A-Z]+/', function ($matches) {
            return strtolower($matches[0]);
        }, $str);
    }

    /**
     * Validate a String Label
     *
     * @param array $labels found host labels
     *
     * @throws InvalidArgumentException If the validation fails
     */
    protected function assertValidHost(array $labels)
    {
        $verifs = array_filter($labels, function ($value) {
            return trim((string) $value) !== '';
        });

        if ($verifs !== $labels) {
            throw new InvalidArgumentException('Invalid Hostname, empty labels are not allowed');
        }

        $this->assertLabelsCount($labels);
        $this->isValidContent($labels);
    }

    /**
     * Validated the Host Label Pattern
     *
     * @param array $data host labels
     *
     * @throws InvalidArgumentException If the validation fails
     */
    protected function isValidContent(array $data)
    {
        if (count(preg_grep('/^[0-9a-z]([0-9a-z-]{0,61}[0-9a-z])?$/i', $data, PREG_GREP_INVERT))) {
            throw new InvalidArgumentException('Invalid Hostname, some labels contain invalid characters');
        }
    }

    /**
     * Validate a Host as an IP
     *
     * @param string $str
     *
     * @return array
     */
    protected function validateIpHost(string $str): array
    {
        $res = $this->filterIpv6Host($str);

        if (is_string($res)) {
            $this->hostAsIpv4 = false;
            $this->hostAsIpv6 = true;

            return [$res];
        }

        if (filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->hostAsIpv4 = true;
            $this->hostAsIpv6 = false;

            return [$str];
        }

        return [];
    }

    /**
     * validate and filter a Ipv6 Hostname
     *
     * @param string $str
     *
     * @return string|false
     */
    protected function filterIpv6Host(string $str)
    {
        preg_match(',^(?P<ldelim>[\[]?)(?P<ipv6>.*?)(?P<rdelim>[\]]?)$,', $str, $matches);

        if (! in_array(strlen($matches['ldelim'] . $matches['rdelim']), [0, 2])) {
            return false;
        }

        if (! strpos($str, '%')) {
            return filter_var($matches['ipv6'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }

        return $this->validateScopedIpv6($matches['ipv6']);
    }

    /**
     * Scope Ip validation according to RFC6874 rules
     *
     * @see http://tools.ietf.org/html/rfc6874#section-2
     * @see http://tools.ietf.org/html/rfc6874#section-4
     *
     * @param string $ip The ip to validate
     *
     * @return string|false
     */
    protected function validateScopedIpv6(string $ip)
    {
        $pos = strpos($ip, '%');

        if (preg_match(',[^\x20-\x7f]|[?#@\[\]],', rawurldecode(substr($ip, $pos)))) {
            return false;
        }

        $ipv6 = substr($ip, 0, $pos);

        if (! $this->isLocalLink($ipv6)) {
            return false;
        }

        $this->hasZoneIdentifier = true;

        return strtolower(rawurldecode($ip));
    }

    /**
     * Tell whether the submitted string is a local link IPv6
     *
     * @param string $ipv6
     *
     * @return bool
     */
    protected function isLocalLink($ipv6): bool
    {
        if (! filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return false;
        }

        $convert = function ($carry, $char) {
            return $carry . str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        };

        $res = array_reduce(str_split(unpack('A16', inet_pton($ipv6))[1]), $convert, '');

        return substr($res, 0, 10) === self::$localLinkPrefix;
    }

    /**
     * Format an IP for string representation of the Host
     *
     * @param string $ipAddress IP address
     *
     * @return string
     */
    protected function formatIp(string $ipAddress): string
    {
        $tmp = explode('%', $ipAddress);

        if (isset($tmp[1])) {
            $ipAddress = $tmp[0] . '%25' . rawurlencode($tmp[1]);
        }

        if ($this->hostAsIpv6) {
            return "[$ipAddress]";
        }

        return $ipAddress;
    }
}
