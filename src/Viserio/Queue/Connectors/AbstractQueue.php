<?php
namespace Viserio\Queue\Connectors;

use Exception;
use Viserio\Contracts\{
    Encryption\Encrypter as EncrypterContract,
    Queue\QueueConnector as QueueConnectorContract
};
use Viserio\Support\Traits\ContainerAwareTrait;

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
     */
    public function pushOn(string $queue, string $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }

    /**
     * {@inheritdoc}
     */
    public function laterOn(string $queue, $delay, string $job, $data = '')
    {
        return $this->later($delay, $job, $data, $queue);
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * Calculate the number of seconds with the given delay.
     *
     * @param int $delay
     *
     * @return int
     */
    protected function getSeconds(int $delay): int
    {
    }

    /**
     * Get the encrypter implementation.
     *
     * @return \Viserio\Contracts\Encryption\Encrypter
     *
     * @throws \Exception
     */
    protected function getEncrypter(): EncrypterContract
    {
        if ($this->encrypter === null) {
            throw new Exception('No encrypter has been set on the Queue.');
        }

        return $this->encrypter;
    }

    /**
     * Set the encrypter implementation.
     *
     * @param  \Viserio\Contracts\Encryption\Encrypter  $encrypter
     *
     * @return void
     */
    public function setEncrypter(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }
}
