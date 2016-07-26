<?php
declare(strict_types=1);
namespace Viserio\Mail;

use Viserio\Support\{
    Invoker,
    Traits\ContainerAwareTrait
};

class QueueMailer extends Mailer
{
    use ContainerAwareTrait;

    /**
     * Call the provided message builder.
     *
     * @param \Closure|string       $callback
     * @param \Viserio\Mail\Message $message
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function callMessageBuilder($callback, $message)
    {
        if ($callback instanceof Closure) {
            return call_user_func($callback, $message);
        }

        if ($this->container !== null) {
            $invoker = (new Invoker())
                ->injectByTypeHint(true)
                ->injectByParameterName(true)
                ->setContainer($this->container);

            return $invoker->call($callback);
        }

        throw new InvalidArgumentException('Callback is not valid.');
    }
}
