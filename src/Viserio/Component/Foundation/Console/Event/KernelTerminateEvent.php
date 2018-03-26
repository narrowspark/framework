<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Event;

use Symfony\Component\Console\Input\InputInterface;
use Viserio\Component\Contract\Events\Event as EventContract;
use Viserio\Component\Contract\Foundation\Terminable as TerminableContract;
use Viserio\Component\Events\Traits\EventTrait;

class KernelTerminateEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new kernel terminate event.
     *
     * @param \Viserio\Component\Contract\Foundation\Terminable $kernel
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param int                                               $status
     */
    public function __construct(TerminableContract $kernel, InputInterface $input, int $status)
    {
        $this->name       = TerminableContract::TERMINATE;
        $this->target     = $kernel;
        $this->parameters = ['input' => $input, 'status' => $status];
    }
}
