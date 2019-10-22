<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Cron;

use Viserio\Contract\Cron\Cron as CronContract;
use Viserio\Contract\Cron\Exception\InvalidArgumentException;
use Viserio\Contract\Cron\Exception\LogicException;

class CallbackCron extends Cron
{
    /**
     * The callback to call.
     *
     * @var callable|string
     */
    protected $callback;

    /**
     * The parameters to pass to the method.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Create a new callback cron instance.
     *
     * @param callable|string $callback
     * @param array           $parameters
     *
     * @throws \Viserio\Contract\Cron\Exception\InvalidArgumentException
     */
    public function __construct($callback, array $parameters = [])
    {
        if (! \is_string($callback) && ! \is_callable($callback)) {
            throw new InvalidArgumentException('Invalid scheduled callback cron job. Must be string or callable.');
        }

        parent::__construct('');

        $this->callback = $callback;
        $this->parameters = $parameters;
    }

    /**
     * Run the given cron job.
     *
     * @return mixed
     */
    public function run()
    {
        if ($this->description) {
            $item = $this->cacheItemPool->getItem($this->getMutexName());
            $item->set($this->getMutexName());
            $item->expiresAfter(1440);

            $this->cacheItemPool->save($item);
        }

        $this->callBeforeCallbacks();

        try {
            $response = $this->getInvoker()->call($this->callback, $this->parameters);
        } finally {
            if ($this->description) {
                $this->cacheItemPool->deleteItem($this->getMutexName());
            }
        }

        $this->callAfterCallbacks();

        return $response;
    }

    /**
     * Do not allow the cron job to overlap each other.
     *
     * @throws \Viserio\Contract\Cron\Exception\LogicException
     *
     * @return \Viserio\Contract\Cron\Cron
     */
    public function withoutOverlapping(): CronContract
    {
        if ($this->description === null) {
            throw new LogicException('A scheduled cron job description is required to prevent overlapping. ' . "Use the 'setDescription' method before 'withoutOverlapping'.");
        }

        return $this->skip(function () {
            return $this->cacheItemPool->hasItem($this->getMutexName());
        });
    }

    /**
     * Get the summary of the event for display.
     *
     * @return string
     */
    public function getSummaryForDisplay(): string
    {
        if (\is_string($this->description)) {
            return $this->description;
        }

        return \is_string($this->callback) ? $this->callback : 'Closure';
    }

    /**
     * Get the mutex name for the scheduled command.
     *
     * @return string
     */
    protected function getMutexName(): string
    {
        return 'schedule-' . \sha1($this->expression . $this->description);
    }
}
