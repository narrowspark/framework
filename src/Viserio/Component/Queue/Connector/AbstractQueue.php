<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Connector;

use Cake\Chronos\Chronos;
use Closure;
use DateTimeInterface;
use Exception;
use Opis\Closure\SerializableClosure;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contract\Queue\QueueConnector as QueueConnectorContract;
use Viserio\Component\Queue\CallQueuedHandler;
use Viserio\Component\Queue\QueueClosure;

abstract class AbstractQueue implements QueueConnectorContract
{
    use ContainerAwareTrait;

    /**
     * The encrypter implementation.
     *
     * @var \Viserio\Component\Contract\Encryption\Encrypter
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
    public function bulk(array $jobs, $data = '', string $queue = null): void
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
     * @param \Viserio\Component\Contract\Encryption\Encrypter $encrypter
     */
    public function setEncrypter(EncrypterContract $encrypter): void
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Calculate the number of seconds with the given delay.
     *
     * @param \DateTimeInterface|int $delay
     *
     * @return int
     */
    protected function getSeconds($delay): int
    {
        if ($delay instanceof DateTimeInterface) {
            return \max(0, $delay->getTimestamp() - $this->getTime());
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
        return Chronos::now()->getTimestamp();
    }

    /**
     * Create a payload string from the given job and data.
     *
     * @param \Closure|object|string $job
     * @param mixed                  $data
     * @param null|string            $queue
     *
     * @return string
     */
    protected function createPayload($job, $data = '', string $queue = null): string
    {
        if ($job instanceof Closure) {
            return \json_encode($this->createClosurePayload($job, $data));
        }

        $encrypter = $this->getEncrypter();

        if (\is_object($job)) {
            return \json_encode([
                'job'  => \sprintf('%s@call', CallQueuedHandler::class),
                'data' => [
                    'commandName' => $encrypter->encrypt(\get_class($job)),
                    'command64'   => $encrypter->encrypt(\base64_encode(\serialize(clone $job))),
                ],
            ]);
        }

        return \json_encode($this->createPlainPayload($job, $data));
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
            \serialize(new SerializableClosure($job))
        );

        return ['job' => QueueClosure::class, 'data' => \compact('closure')];
    }

    /**
     * Get the encrypter implementation.
     *
     * @throws \Exception
     *
     * @return \Viserio\Component\Contract\Encryption\Encrypter
     */
    protected function getEncrypter(): EncrypterContract
    {
        if ($this->encrypter === null) {
            throw new Exception('No encrypter has been set on the Queue.');
        }

        return $this->encrypter;
    }
}
