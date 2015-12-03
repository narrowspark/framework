<?php
namespace Viserio\Contracts\Queue;

interface Consumer
{
    /**
     * @param $event
     */
    public function consume($event);
}
