<?php
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use ReflectionClass;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Session\SessionHandler as SessionHandlerContract;
use Viserio\Encryption\Encrypter;
use Viserio\Session\Store;

class StoreTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    const SESSION_ID = 'a2a4b6abagataeaaza6aa4raaaaaaaaaaaaaaaaa';

    private $encrypter;
    private $session;

    public function setUp()
    {
        parent::setUp();

        $reflection = new ReflectionClass(Store::class);
        $this->encrypter = new Encrypter(Key::createNewRandomKey());

        $this->session = $reflection->newInstanceArgs(
            [
                'name',
                $this->mock(SessionHandlerContract::class),
                $this->encrypter,
            ]
        );
    }

    public function testSessionIsLoadedFromHandler()
    {
        $session = $this->session;
        $session->getHandler()
            ->shouldReceive('read')
            ->once()
            ->with(self::SESSION_ID)
            ->andReturn(
                $this->encrypter->encrypt(
                    json_encode(
                        ['foo' => 'bar', 'bagged' => ['name' => 'viserio']]
                    )
                )
            );
        $session->start();

        $this->assertEquals('bar', $session->get('foo'));
    }
}
