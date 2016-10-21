<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Encryption\Encrypter;
use Viserio\Session\SessionManager;

class SessionManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $encrypter = new Encrypter(Key::createNewRandomKey());

        $this->manager = new SessionManager($this->mock(ConfigContract::class), $encrypter);
    }
}
