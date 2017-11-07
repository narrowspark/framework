<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Session\EncryptedStore;

class EncryptedStoreTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Encryption\Encrypter
     */
    private $encrypter;

    /**
     * @var \Viserio\Component\Session\EncryptedStore
     */
    private $session;

    /**
     * @var \SessionHandlerInterface|\Mockery\MockInterface
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $password        = \random_bytes(32);
        $this->encrypter = new Encrypter(KeyFactory::generateKey($password));
        $this->handler   = $this->mock(SessionHandlerContract::class);
        $this->session   = new EncryptedStore('name', $this->handler, $this->encrypter);
    }
}
