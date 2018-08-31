<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler;

use Viserio\Component\Contract\Profiler\DataCollector as DataCollectorContract;
use Viserio\Component\Contract\Profiler\Exception\CollectorNotFoundException;

class Profile
{
    /**
     * Profile token.
     *
     * @var string
     */
    private $token;

    /**
     * Client ip.
     *
     * @var string
     */
    private $ip;

    /**
     * Request method.
     *
     * @var string
     */
    private $method;

    /**
     * Request url.
     *
     * @var string
     */
    private $url;

    /**
     * Needed time.
     *
     * @var string
     */
    private $time;

    /**
     * Creation date.
     *
     * @var string
     */
    private $date;

    /**
     * Response status code.
     *
     * @var string
     */
    private $statusCode;

    /**
     * All collected collectors.
     *
     * @var array
     */
    private $collectors = [];

    /**
     * Create new Profiler profile.
     *
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore
     */
    public function __sleep(): array
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
            'date',
        ];
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
     * Sets the token.
     *
     * @param string $token The token
     *
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
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
     *
     * @return void
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
     * Set the request method.
     *
     * @param string $method
     *
     * @return void
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * Returns the url.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the request url.
     *
     * @param string $url
     *
     * @return void
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
     * Set the needed time.
     *
     * @param float|string $time
     *
     * @return void
     */
    public function setTime($time): void
    {
        $this->time = (string) $time;
    }

    /**
     * Returns the date.
     *
     * @return string The date
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * Set the creation date.
     *
     * @param string $date
     *
     * @return void
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    /**
     * Get the response status code.
     *
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    /**
     * Set the response status code.
     *
     * @param int $statusCode
     *
     * @return void
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = (string) $statusCode;
    }

    /**
     * Gets the Collectors associated with this profile.
     *
     * @return \Viserio\Component\Contract\Profiler\DataCollector[]
     */
    public function getCollectors(): array
    {
        return $this->collectors;
    }

    /**
     * Sets the Collectors associated with this profile.
     *
     * @param array $collectors
     *
     * @return void
     */
    public function setCollectors(array $collectors): void
    {
        foreach ($collectors as $collector) {
            $this->addCollector($collector['collector']);
        }
    }

    /**
     * Gets a Collector by name.
     *
     * @param string $name A collector name
     *
     * @throws \Viserio\Component\Contract\Profiler\Exception\CollectorNotFoundException if the collector does not exist
     *
     * @return \Viserio\Component\Contract\Profiler\DataCollector
     */
    public function getCollector(string $name): DataCollectorContract
    {
        if (! isset($this->collectors[$name])) {
            throw new CollectorNotFoundException(\sprintf('Collector [%s] not found.', $name));
        }

        return $this->collectors[$name];
    }

    /**
     * Adds a Collector.
     *
     * @param \Viserio\Component\Contract\Profiler\DataCollector $collector
     *
     * @return void
     */
    public function addCollector(DataCollectorContract $collector): void
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
