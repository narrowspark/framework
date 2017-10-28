<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Cake\Chronos\Chronos;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\HiddenString;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Session\Fingerprint\UserAgentGenerator;
use Viserio\Component\Session\Store;

class StoreTest extends MockeryTestCase
{
    public const SESSION_ID = 'cfdddff0a844531c4a985eae2806a8c761b754df';

    /**
     * @var \Viserio\Component\Encryption\Encrypter
     */
    private $encrypter;

    private $encryptString;

    private $session;

    public function setUp(): void
    {
        parent::setUp();

        $reflection      = new ReflectionClass(Store::class);
        $password        = \random_bytes(32);
        $this->encrypter = new Encrypter(KeyFactory::generateKey($password));

        $this->session = $reflection->newInstanceArgs(
            [
                'name',
                $this->mock(SessionHandlerContract::class),
                $this->encrypter,
            ]
        );

        $this->encryptString = $this->encrypter->encrypt(new HiddenString(
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
        ));
    }

    public function testSessionIsLoadedFromHandler(): void
    {
        $session       = $this->session;
        $encryptString = $this->encrypter->encrypt(new HiddenString(
            \json_encode(
                [
                    'foo'          => 'bar',
                    'bagged'       => ['name' => 'viserio'],
                    '__metadata__' => [
                        'firstTrace'        => 0,
                        'lastTrace'         => 0,
                        'regenerationTrace' => 0,
                        'requestsCount'     => 0,
                        'fingerprint'       => '',
                    ],
                ],
                \JSON_PRESERVE_ZERO_FRACTION
            )
        ));
        $session->getHandler()
            ->shouldReceive('read')
            ->once()
            ->andReturn($encryptString);
        $session->setId(self::SESSION_ID);

        $session->open();

        self::assertEquals('bar', $session->get('foo'));
        self::assertTrue($session->isStarted());

        $session->getHandler()
            ->shouldReceive('write')
            ->once();

        $session->save();

        self::assertFalse($session->isStarted());
    }

    public function testSaveDontSaveIfSessionIsNotStarted(): void
    {
        $session = $this->session;

        self::assertFalse($session->isStarted());

        // save dont work if no session is started.
        $session->save();

        $session->getHandler()
            ->shouldReceive('write')
            ->never();
    }

    /**
     * @expectedException \Viserio\Component\Contract\Session\Exception\SuspiciousOperationException
     */
    public function testSessionHasSuspiciousFingerPrint(): void
    {
        $encryptString = $this->encrypter->encrypt(new HiddenString(
            \json_encode(
                [
                    'foo'          => 'bar',
                    'bagged'       => ['name' => 'viserio'],
                    '__metadata__' => [
                        'firstTrace'        => 0,
                        'lastTrace'         => 0,
                        'regenerationTrace' => 0,
                        'requestsCount'     => 0,
                        'fingerprint'       => 'foo',
                    ],
                ],
                \JSON_PRESERVE_ZERO_FRACTION
            )
        ));
        $session = $this->session;
        $session->getHandler()
            ->shouldReceive('read')
            ->once()
            ->andReturn($encryptString);
        $session->setId(self::SESSION_ID);
        $session->open();
    }

    public function testSessionReturnsFalseOnFirstTraceNull(): void
    {
        $session = $this->session;
        $session->getHandler()
            ->shouldReceive('read')
            ->once()
            ->andReturn($this->encrypter->encrypt(new HiddenString('')));
        $session->setId(self::SESSION_ID);

        self::assertFalse($session->open());
    }

    public function testName(): void
    {
        $session = $this->session;

        self::assertEquals($session->getName(), 'name');

        $session->setName('foo');

        self::assertEquals($session->getName(), 'foo');
    }

    public function testSessionMigration(): void
    {
        $session = $this->session;
        $session->start();

        $oldId = $session->getId();
        $session->getHandler()->shouldReceive('destroy')->never();

        self::assertTrue($session->migrate());
        self::assertNotEquals($oldId, $session->getId());

        $session = $this->session;
        $oldId   = $session->getId();
        $session->getHandler()->shouldReceive('destroy')->once()->with($oldId);

        self::assertTrue($session->migrate(true));
        self::assertNotEquals($oldId, $session->getId());
    }

    public function testCantSetInvalidId(): void
    {
        $session = $this->session;

        $session->setId('wrong');

        self::assertNotEquals('wrong', $session->getId());
    }

    public function testSessionInvalidate(): void
    {
        $session = $this->session;
        $session->start();
        $session->set('foo', 'bar');

        $oldId = $session->getId();

        self::assertGreaterThan(0, \count($session->getAll()));

        $session->getHandler()->shouldReceive('destroy')->once()->with($oldId);

        self::assertTrue($session->invalidate());
        self::assertFalse($session->has('foo'));
        self::assertNotEquals($oldId, $session->getId());
        self::assertCount(0, $session->getAll());
    }

    public function testCanGetRequestsCount(): void
    {
        $session = $this->session;

        self::assertEquals(0, $session->getRequestsCount());

        $session->start();

        self::assertEquals(1, $session->getRequestsCount());
    }

    /**
     * @expectedException \Viserio\Component\Contract\Session\Exception\SessionNotStartedException
     * @expectedExceptionMessage The session is not started.
     */
    public function testSetMethodToThrowException(): void
    {
        $session = $this->session;
        $session->set('foo', 'bar');
    }

    public function testSetAndGetPreviousUrl(): void
    {
        $session = $this->session;
        $session->start();
        $session->setPreviousUrl('/test');

        self::assertSame('/test', $session->getPreviousUrl());
    }

    public function testStartMethodResetsLastTraceAndFirstTrace(): void
    {
        $session = $this->encryptedSession();

        self::assertTrue($session->isExpired());

        $session->open();

        $lastTrace  = $session->getLastTrace();
        $firstTrace = $session->getLastTrace();

        $session->start();

        self::assertFalse($session->isExpired());
        self::assertNotEquals($lastTrace, $session->getLastTrace());
        self::assertNotEquals($firstTrace, $session->getFirstTrace());
    }

    public function testStartMethodResetsRequestsCount(): void
    {
        $session = $this->session;
        $session->start();

        self::assertEquals(1, $session->getRequestsCount());
    }

    public function testStartMethodResetsIdRegenerationTrace(): void
    {
        $session = $this->encryptedSession();
        $session->open();

        $regenerationTrace = $session->getRegenerationTrace();

        $session->start();

        self::assertNotEquals($regenerationTrace, $session->getRegenerationTrace());
        self::assertGreaterThanOrEqual(Chronos::now()->getTimestamp() - 1, $session->getRegenerationTrace());
    }

    public function testStartMethodGeneratesFingerprint(): void
    {
        $session = $this->session;
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => 'test']);

        $oldFingerprint = $session->getFingerprint();

        $session->addFingerprintGenerator(new UserAgentGenerator($request));

        $session->start();

        self::assertSame('', $oldFingerprint);
        self::assertEquals(40, \mb_strlen($session->getFingerprint()));
        self::assertNotEquals($oldFingerprint, $session->getFingerprint());
    }

    public function testStartMethodOpensSession(): void
    {
        $session = $this->session;

        $session->start();

        self::assertTrue($session->isStarted());
    }

    public function testRemove(): void
    {
        $session = $this->session;
        $session->start();
        $session->set('foo', 'bar');

        $pulled = $session->remove('foo');

        self::assertFalse($session->has('foo'));
        self::assertEquals('bar', $pulled);
    }

    public function testClear(): void
    {
        $session = $this->session;
        $session->start();
        $session->set('foo', 'bar');
        $session->clear();

        self::assertFalse($session->has('foo'));
    }

    public function testSessionIdShouldBeRegeneratedIfIdRequestsLimitReached(): void
    {
        $readValue = $this->encrypter->encrypt(new HiddenString(''));
        $session   = $this->session;
        $session->setIdRequestsLimit(3);
        $session->getHandler()
            ->shouldReceive('read')
            ->times(3)
            ->andReturn($readValue);
        $session->getHandler()
            ->shouldReceive('write')
            ->times(3);
        $session->getHandler()
            ->shouldReceive('destroy')
            ->once();

        $session->start();
        $session->open();

        self::assertSame(1, $session->getRequestsCount());

        $session->save();

        self::assertTrue($session->open());

        self::assertSame(2, $session->getRequestsCount());

        $session->save();

        self::assertTrue($session->open());

        self::assertSame(3, $session->getRequestsCount());

        $session->save();
        // Session should migrate to a new one
        self::assertTrue($session->open());

        self::assertSame(1, $session->getRequestsCount());
    }

    public function testSessionIdShouldBeRegeneratedIfIdTtlLimitReached(): void
    {
        $session = $this->session;
        $session->setId(self::SESSION_ID);
        $session->getHandler()
            ->shouldReceive('read')
            ->twice()
            ->andReturn($this->encryptString);
        $session->setIdLiveTime(5);
        $session->getHandler()
            ->shouldReceive('write')
            ->times(1);
        $session->getHandler()
            ->shouldReceive('destroy')
            ->times(1);
        $session->open();

        self::assertSame(1, $session->getRequestsCount());
        self::assertSame(self::SESSION_ID, $session->getId());

        \sleep(10);

        $session->save();
        $session->open();

        self::assertNotSame(self::SESSION_ID, $session->getId());
    }

    public function testDataFlashing(): void
    {
        $session = $this->session;
        $session->start();
        $session->flash('foo', 'bar');
        $session->flash('bar', 0);

        self::assertTrue($session->has('foo'));
        self::assertEquals('bar', $session->get('foo'));
        self::assertEquals(0, $session->get('bar'));

        $session->ageFlashData();

        self::assertTrue($session->has('foo'));
        self::assertEquals('bar', $session->get('foo'));
        self::assertEquals(0, $session->get('bar'));

        $session->ageFlashData();

        self::assertFalse($session->has('foo'));
        self::assertNull($session->get('foo'));
    }

    public function testDataFlashingNow(): void
    {
        $session = $this->session;
        $session->start();
        $session->now('foo', 'bar');
        $session->now('bar', 0);

        self::assertTrue($session->has('foo'));
        self::assertEquals('bar', $session->get('foo'));
        self::assertEquals(0, $session->get('bar'));

        $session->ageFlashData();

        self::assertFalse($session->has('foo'));
        self::assertNull($session->get('foo'));
    }

    public function testDataMergeNewFlashes(): void
    {
        $session = $this->session;
        $session->start();
        $session->flash('foo', 'bar');
        $session->set('fu', 'baz');
        $session->set('_flash.old', ['qu']);

        self::assertNotFalse(\array_search('foo', $session->get('_flash.new'), true));
        self::assertFalse(\array_search('fu', $session->get('_flash.new'), true));

        $session->keep(['fu', 'qu']);

        self::assertNotFalse(\array_search('foo', $session->get('_flash.new'), true));
        self::assertNotFalse(\array_search('fu', $session->get('_flash.new'), true));
        self::assertNotFalse(\array_search('qu', $session->get('_flash.new'), true));
        self::assertFalse(\array_search('qu', $session->get('_flash.old'), true));
    }

    public function testReflash(): void
    {
        $session = $this->session;
        $session->start();
        $session->flash('foo', 'bar');
        $session->set('_flash.old', ['foo']);
        $session->reflash();

        self::assertNotFalse(\array_search('foo', $session->get('_flash.new'), true));
        self::assertFalse(\array_search('foo', $session->get('_flash.old'), true));
    }

    public function testReflashWithNow(): void
    {
        $session = $this->session;
        $session->start();
        $session->now('foo', 'bar');
        $session->reflash();

        self::assertNotFalse(\array_search('foo', $session->get('_flash.new'), true));
        self::assertFalse(\array_search('foo', $session->get('_flash.old'), true));
    }

    public function testIfSessionCanBeJsonSerialized(): void
    {
        $session = $this->session;

        self::assertSame([], $session->jsonSerialize());
    }

    private function encryptedSession()
    {
        $session = $this->session;
        $session->setId(self::SESSION_ID);
        $session->getHandler()
            ->shouldReceive('read')
            ->once()
            ->andReturn($this->encryptString);

        return $session;
    }
}
