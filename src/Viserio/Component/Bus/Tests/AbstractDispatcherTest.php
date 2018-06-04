<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests;

/**
 * @internal
 */
abstract class AbstractDispatcherTest
{
    /**
     * @var \Viserio\Component\Contract\Bus\Dispatcher|\Viserio\Component\Contract\Bus\QueueingDispatcher
     */
    protected $dispatcher;
}
