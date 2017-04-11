<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Events;

use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Events\Traits\EventTrait;

class LocaleChangedEvent implements EventContract
{
    use EventTrait;

    /**
     * Create a new bootstrapped event.
     *
     * @param string                                              $name
     * @param \Viserio\Component\Contracts\Foundation\Application $app
     * @param string                                              $locale
     */
    public function __construct(string $name, ApplicationContract $app, string $locale)
    {
        $this->name   = 'locale.changed';
        $this->target = $app;
        $this->param  = ['locale' => $locale];
    }
}