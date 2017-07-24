<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PhpInfoDataCollector extends AbstractDataCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $this->data = [
            'php_version'      => PHP_VERSION,
            'php_architecture' => PHP_INT_SIZE * 8,
            'php_timezone'     => \date_default_timezone_get(),
            'sapi_name'        => PHP_SAPI,
        ];

        if (\preg_match('~^(\d+(?:\.\d+)*)(.+)?$~', $this->data['php_version'], $matches) && isset($matches[2])) {
            // @codeCoverageIgnoreStart
            $this->data['php_version']       = $matches[1];
            $this->data['php_version_extra'] = $matches[2];
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'label' => 'PHP Version',
            'value' => $this->data['php_version'] . $this->data['php_version_extra'] ?? '',
        ];
    }

    /**
     * Gets the PHP version.
     *
     * @return null|string The PHP version
     */
    public function getPhpVersion(): ?string
    {
        return $this->data['php_version'];
    }

    /**
     * Gets the PHP version extra part.
     *
     * @return null|string The extra part
     */
    public function getPhpVersionExtra(): ?string
    {
        return $this->data['php_version_extra'] ?? null;
    }

    /**
     * The PHP architecture as number of bits (e.g. 32 or 64).
     *
     * @return null|int
     */
    public function getPhpArchitecture(): ?int
    {
        return $this->data['php_architecture'];
    }

    /**
     * @return string
     */
    public function getPhpTimezone(): ?string
    {
        return $this->data['php_timezone'];
    }
}
