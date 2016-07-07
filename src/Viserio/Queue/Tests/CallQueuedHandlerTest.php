<?php
namespace Viserio\Queue\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\{
    Encryption\Encrypter as EncrypterContract,
    Bus\Dispatcher as DispatcherContract
};
use Viserio\Queue\CallQueuedHandler;

class CallQueuedHandlerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
}
