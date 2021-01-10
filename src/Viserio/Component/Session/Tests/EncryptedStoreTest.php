<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Session\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Component\Session\EncryptedStore;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class EncryptedStoreTest extends MockeryTestCase
{
    private const SESSION_ID = 'cfdddff0a844531c4a985eae2806a8c761b754df';

    /** @var \ParagonIE\Halite\Symmetric\EncryptionKey */
    private $key;

    /** @var \Viserio\Component\Session\EncryptedStore */
    private $session;

    /** @var \Mockery\MockInterface|SessionHandlerContract */
    private $handler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->key = KeyFactory::generateEncryptionKey();
        $this->handler = Mockery::mock(SessionHandlerContract::class);
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
                                'foo' => 'bar',
                                'bagged' => ['name' => 'viserio'],
                                '__metadata__' => [
                                    'firstTrace' => 0,
                                    'lastTrace' => 0,
                                    'regenerationTrace' => 1,
                                    'requestsCount' => 0,
                                    'fingerprint' => '',
                                ],
                            ],
                            \JSON_PRESERVE_ZERO_FRACTION
                        )
                    ),
                    $this->key
                )
            );

        self::assertTrue($session->isExpired());

        $session->open();

        $lastTrace = $session->getLastTrace();
        $firstTrace = $session->getLastTrace();

        $session->start();

        self::assertFalse($session->isExpired());
        self::assertNotEquals($lastTrace, $session->getLastTrace());
        self::assertNotEquals($firstTrace, $session->getFirstTrace());
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
