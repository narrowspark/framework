<?php
declare(strict_types=1);
namespace Viserio\Component\Queue;

use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contracts\Queue\Job as JobContract;

class QueueClosure
{
    /**
     * The encrypter instance.
     *
     * @var \Viserio\Component\Contracts\Encryption\Encrypter
     */
    protected $crypt;

    /**
     * Create a new queued Closure job.
     *
     * @param \Viserio\Component\Contracts\Encryption\Encrypter $crypt
     */
    public function __construct(EncrypterContract $crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * Run the Closure based queue job.
     *
     * @param \Viserio\Component\Contracts\Queue\Job $job
     * @param array                                  $data
     */
    public function run(JobContract $job, array $data)
    {
        $closure = unserialize($this->crypt->decrypt($data['closure']));

        $closure($job);
    }
}
