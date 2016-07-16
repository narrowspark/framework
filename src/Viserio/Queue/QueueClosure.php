<?php
namespace Viserio\Queue;

use Viserio\Contracts\{
    Encryption\Encrypter as EncrypterContract,
    Queue\Job as JobContract
};

class QueueClosure
{
     /**
     * The encrypter instance.
     *
     * @var \Viserio\Contracts\Encryption\Encrypter
     */
    protected $crypt;

    /**
     * Create a new queued Closure job.
     *
     * @param \Viserio\Contracts\Encryption\Encrypter $crypt
     */
    public function __construct(EncrypterContract $crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * Run the Closure based queue job.
     *
     * @param \Viserio\Contracts\Queue\Job $job
     * @param array                        $data
     *
     * @return void
     */
    public function run(JobContract $job, array $data)
    {
        $closure = unserialize($this->crypt->decrypt($data['closure']));

        $closure($job);
    }
}
