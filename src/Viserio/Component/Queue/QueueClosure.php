<?php
declare(strict_types=1);
namespace Viserio\Component\Queue;

use Viserio\Component\Contract\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contract\Queue\Job as JobContract;

class QueueClosure
{
    /**
     * The encrypter instance.
     *
     * @var \Viserio\Component\Contract\Encryption\Encrypter
     */
    protected $crypt;

    /**
     * Create a new queued Closure job.
     *
     * @param \Viserio\Component\Contract\Encryption\Encrypter $crypt
     */
    public function __construct(EncrypterContract $crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * Run the Closure based queue job.
     *
     * @param \Viserio\Component\Contract\Queue\Job $job
     * @param array                                 $data
     */
    public function run(JobContract $job, array $data): void
    {
        $closure = \unserialize($this->crypt->decrypt($data['closure']));

        $closure($job);
    }
}
