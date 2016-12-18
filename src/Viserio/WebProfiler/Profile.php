<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use InvalidArgumentException;
use Viserio\Contracts\WebProfiler\DataCollector as DataCollectorContract;

class Profile
{
    /**
     * Profile token.
     *
     * @var string
     */
    private $token;

    /**
     * [$ip description].
     *
     * @var string
     */
    private $ip;

    /**
     * [$method description].
     *
     * @var string
     */
    private $method;

    /**
     * [$url description].
     *
     * @var string
     */
    private $url;

    /**
     * [$time description].
     *
     * @var string
     */
    private $time;

    /**
     * [$statusCode description].
     *
     * @var string
     */
    private $statusCode;

    /**
     * [$collectors description].
     *
     * @var array
     */
    private $collectors = [];

    /**
     * [__sleep description].
     *
     * @return array
     */
    public function __sleep()
    {
        return [
            'token',
            'parent',
            'children',
            'collectors',
            'ip',
            'method',
            'url',
            'time',
            'statusCode',
        ];
    }

    /**
     * Sets the token.
     *
     * @param string $token The token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Gets the token.
     *
     * @return string The token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Returns the IP.
     *
     * @return string The IP
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * Sets the IP.
     *
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * Returns the request method.
     *
     * @return string The request method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * [setMethod description].
     *
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * Returns the URL.
     *
     * @return string The URL
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * [setUrl description].
     *
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * Returns the time.
     *
     * @return string The time
     */
    public function getTime(): string
    {
        if ($this->time === null) {
            return '0';
        }

        return $this->time;
    }

    /**
     * [setTime description].
     *
     * @param float $time
     */
    public function setTime(float $time): void
    {
        $this->time = $time;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = (string) $statusCode;
    }

    /**
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    /**
     * Gets a Collector by name.
     *
     * @param string $name A collector name
     *
     * @throws \InvalidArgumentException if the collector does not exist
     *
     * @return \Viserio\Contracts\WebProfiler\DataCollector
     */
    public function getCollector($name)
    {
        if (! isset($this->collectors[$name])) {
            throw new InvalidArgumentException(sprintf('Collector "%s" does not exist.', $name));
        }

        return $this->collectors[$name];
    }

    /**
     * Gets the Collectors associated with this profile.
     *
     * @return \Viserio\Contracts\WebProfiler\DataCollector[]
     */
    public function getCollectors(): array
    {
        return $this->collectors;
    }

    /**
     * Sets the Collectors associated with this profile.
     *
     * @param \Viserio\Contracts\WebProfiler\DataCollector[] $collectors
     */
    public function setCollectors(array $collectors)
    {
        foreach ($collectors as $collector) {
            $this->addCollector($collector);
        }
    }

    /**
     * Adds a Collector.
     *
     * @param \Viserio\Contracts\WebProfiler\DataCollector $collector
     */
    public function addCollector(DataCollectorContract $collector)
    {
        $this->collectors[$collector->getName()] = $collector;
    }

    /**
     * Returns true if a Collector for the given name exists.
     *
     * @param string $name A collector name
     *
     * @return bool
     */
    public function hasCollector(string $name): bool
    {
        return isset($this->collectors[$name]);
    }
}
