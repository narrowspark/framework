<?php
namespace Viserio\Queue\Connectors;

use Narrowspark\Arr\StaticArr as Arr;
use IronMQ\IronMQ;
use Psr\Http\Message\RequestInterface;

class IronQueue extends AbstractQueue
{
    /**
     * The IronMQ instance.
     *
     * @var \IronMQ\IronMQ
     */
    protected $iron;

    /**
     * The current request instance.
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * Number of seconds before the reservation_id times out on a newly popped message.
     *
     * @var int
     */
    protected $timeout;

    /**
     * Create a new IronMQ queue instance.
     *
     * @param \IronMQ\IronMQ                     $iron
     * @param \Psr\Http\Message\RequestInterface $request
     * @param string                             $default
     * @param int                                $timeout
     */
    public function __construct(IronMQ $iron, RequestInterface $request, string $default, int $timeout = 60)
    {
        $this->iron = $iron;
        $this->request = $request;
        $this->default = $default;
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function push(string $job, $data = '', string $queue = null)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function pushRaw(string $payload, string $queue = null, array $options = [])
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function later($delay, string $job, $data = '', string $queue = null)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function pop(string $queue = null)
    {
        //
    }

    /**
     * Get the underlying IronMQ instance.
     *
     * @return \IronMQ\IronMQ
     */
    public function getIron()
    {
        return $this->iron;
    }

    /**
     * Get the request instance.
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
