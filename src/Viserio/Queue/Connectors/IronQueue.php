<?php
namespace Viserio\Queue\Connectors;

use Narrowspark\Arr\StaticArr as Arr;
use IronMQ\IronMQ;

class IronQueue extends AbstractQueue
{
    /**
     * The IronMQ instance.
     *
     * @var \IronMQ\IronMQ
     */
    protected $iron;
    /**
     * Number of seconds before the reservation_id times out on a newly popped message.
     *
     * @var int
     */
    protected $timeout;

    /**
     * Create a new IronMQ queue instance.
     *
     * @param \IronMQ\IronMQ $iron
     * @param string         $default
     * @param int            $timeout
     */
    public function __construct(IronMQ $iron, string $default, int $timeout = 60)
    {
        $this->iron = $iron;
        $this->default = $default;
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function push($job, $data = '', string $queue = null)
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
    public function later($delay, $job, $data = '', string $queue = null)
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
    public function getIron(): IronMQ
    {
        return $this->iron;
    }
}
