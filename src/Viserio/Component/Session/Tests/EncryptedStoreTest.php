<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\HiddenString;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Session\EncryptedStore;

class EncryptedStoreTest extends MockeryTestCase
{
    private const SESSION_ID = 'cfdddff0a844531c4a985eae2806a8c761b754df';

    /**
     * @var \Viserio\Component\Encryption\Encrypter
     */
    private $encrypter;

    /**
     * @var \Viserio\Component\Session\EncryptedStore
     */
    private $session;

    /**
     * @var \Mockery\MockInterface|\SessionHandlerInterface
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

    public function testStartMethodResetsLastTraceAndFirstTrace(): void
    {
        $session = $this->session;
        $session->setId(self::SESSION_ID);

        $this->handler->shouldReceive('read')
            ->once()
            ->andReturn(
                $this->encrypter->encrypt(new HiddenString(
                    \json_encode(
                        [
                            'foo'          => 'bar',
                            'bagged'       => ['name' => 'viserio'],
                            '__metadata__' => [
                                'firstTrace'        => 0,
                                'lastTrace'         => 0,
                                'regenerationTrace' => 1,
                                'requestsCount'     => 0,
                                'fingerprint'       => '',
                            ],
                        ],
                        \JSON_PRESERVE_ZERO_FRACTION
                    )
                ))
            );

        self::assertTrue($session->isExpired());

        $session->open();

        $lastTrace  = $session->getLastTrace();
        $firstTrace = $session->getLastTrace();

        $session->start();

        self::assertFalse($session->isExpired());
        self::assertNotEquals($lastTrace, $session->getLastTrace());
        self::assertNotEquals($firstTrace, $session->getFirstTrace());
    }

    /**
     * {@inheritdoc}
     */
    protected function assertPreConditions(): void
    {
        parent::assertPreConditions();

        $this->allowMockingNonExistentMethods(true);
    }
}
