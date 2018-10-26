<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Component\Session\EncryptedStore;

/**
 * @internal
 */
final class EncryptedStoreTest extends MockeryTestCase
{
    private const SESSION_ID = 'cfdddff0a844531c4a985eae2806a8c761b754df';

    /**
     * @var \ParagonIE\Halite\Symmetric\EncryptionKey
     */
    private $key;

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->key     = KeyFactory::generateEncryptionKey();
        $this->handler = $this->mock(SessionHandlerContract::class);
        $this->session = new EncryptedStore('name', $this->handler, $this->key);
    }

    public function testStartMethodResetsLastTraceAndFirstTrace(): void
    {
        $session = $this->session;
        $session->setId(self::SESSION_ID);

        $this->handler->shouldReceive('read')
            ->once()
            ->andReturn(
                Crypto::encrypt(
                    new HiddenString(
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
                    ),
                    $this->key
                )
            );

        $this->assertTrue($session->isExpired());

        $session->open();

        $lastTrace  = $session->getLastTrace();
        $firstTrace = $session->getLastTrace();

        $session->start();

        $this->assertFalse($session->isExpired());
        $this->assertNotEquals($lastTrace, $session->getLastTrace());
        $this->assertNotEquals($firstTrace, $session->getFirstTrace());
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
