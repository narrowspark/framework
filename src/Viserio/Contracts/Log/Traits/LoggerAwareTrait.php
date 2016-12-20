<?php
declare(strict_types=1);
namespace Viserio\Contracts\Log\Traits;

use Psr\Log\LoggerInterface as PsrLoggerInterface;
use RuntimeException;

trait LoggerAwareTrait
{
    /**
     * Event dispatcher instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Set a Logger instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(PsrLoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Get the Logger instance.
     *
     * @throws \RuntimeException
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): PsrLoggerInterface
    {
        if (!$this->logger) {
            throw new RuntimeException('Logger is not set up.');
        }

        return $this->logger;
    }
}
