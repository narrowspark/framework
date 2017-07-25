<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests;

abstract class AbstractDispatcherTest
{
    /**
     * @var \Viserio\Component\Contracts\Bus\Dispatcher|\Viserio\Component\Contracts\Bus\QueueingDispatcher
     */
    protected $dispatcher;
}
