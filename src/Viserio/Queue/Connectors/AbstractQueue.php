<?php
declare(strict_types=1);
namespace Viserio\Queue\Connectors;

use Closure;
use DateTimeInterface;
use Exception;
use Opis\Closure\SerializableClosure;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Contracts\Queue\QueueConnector as QueueConnectorContract;
use Viserio\Queue\CallQueuedHandler;
use Viserio\Queue\QueueClosure;

abstract class AbstractQueue implements QueueConnectorContract
{
    use ContainerAwareTrait;

    /**
     * The encrypter implementation.
     *
     * @var \Viserio\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function pushOn(string $queue, $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function laterOn(string $queue, $delay, $job, $data = '')
    {
        return $this->later($delay, $job, $data, $queue);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function bulk(array $jobs, $data = '', string $queue = null)
    {
        foreach ($jobs as $job) {
            $this->push($job, $data, $queue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue($queue): string
    {
        return $queue ?? $this->default;
    }

    /**
     * Set the encrypter implementation.
     *
     * @param \Viserio\Contracts\Encryption\Encrypter $encrypter
     */
    public function setEncrypter(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Calculate the number of seconds with the given delay.
     *
     * @param int|\DateTimeInterface $delay
     *
     * @return int
     */
    protected function getSeconds($delay): int
    {
        if ($delay instanceof DateTimeInterface) {
            return max(0, $delay->getTimestamp() - $this->getTime());
        }

        return (int) $delay;
    }

    /**
     * Get the current UNIX timestamp.
     *
     * @return int
     *
     * @codeCoverageIgnore
     */
    protected function getTime(): int
    {
        return time();
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param string|object|\Closure $job
     * @param mixed                  $data
     * @param string|null            $queue
     *
     * @return string
     */
    protected function createPayload($job, $data = '', string $queue = null): string
    {
        if ($job instanceof Closure) {
            return json_encode($this->createClosurePayload($job, $data));
        }

        $encrypter = $this->getEncrypter();

        if (is_object($job)) {
            return json_encode([
                'job' => sprintf('%s@call', CallQueuedHandler::class),
                'data' => [
                    'commandName' => $encrypter->encrypt(get_class($job)),
                    'command' => $encrypter->encrypt(serialize(clone $job)),
                ],
            ]);
        }

        return json_encode($this->createPlainPayload($job, $data));
    }

    /**
     * Create a typical, "plain" queue payload array.
     *
     * @param string $job
     * @param mixed  $data
     *
     * @return array
     */
    protected function createPlainPayload(string $job, $data): array
    {
        return ['job' => $job, 'data' => $data];
    }

    /**
     * Create a payload string for the given Closure job.
     *
     * @param \Closure $job
     * @param mixed    $data
     *
     * @return array
     */
    protected function createClosurePayload(Closure $job, $data): array
    {
        $closure = $this->getEncrypter()->encrypt(
            serialize(new SerializableClosure($job))
        );

        return ['job' => QueueClosure::class, 'data' => compact('closure')];
    }

    /**
     * Get the encrypter implementation.
     *
     * @throws \Exception
     *
     * @return \Viserio\Contracts\Encryption\Encrypter
     */
    protected function getEncrypter(): EncrypterContract
    {
        if ($this->encrypter === null) {
            throw new Exception('No encrypter has been set on the Queue.');
        }

        return $this->encrypter;
    }
}
