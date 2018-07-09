<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Cake\Chronos\Chronos;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface;
use Viserio\Component\Session\Fingerprint\UserAgentGenerator;
use Viserio\Component\Session\Store;

/**
 * @internal
 */
final class StoreTest extends MockeryTestCase
{
    private const SESSION_ID = 'cfdddff0a844531c4a985eae2806a8c761b754df';

    /**
     * @var \Viserio\Component\Session\Store
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

        $this->handler = $this->mock(SessionHandlerInterface::class);
        $this->session = new Store('name', $this->handler);
    }

    public function testSessionIsLoadedFromHandler(): void
    {
        $this->handler->shouldReceive('read')
            ->once()
            ->andReturn($this->getSessionInfoAsJsonString());

        $this->session->setId(self::SESSION_ID);
        $this->session->open();

        static::assertEquals('bar', $this->session->get('foo'));
        static::assertTrue($this->session->isStarted());

        $this->handler->shouldReceive('write')
            ->once()
            ->with(self::SESSION_ID, \Mockery::type('string'));

        $this->session->save();

        static::assertFalse($this->session->isStarted());
    }

    public function testDontSaveIfSessionIsNotStarted(): void
    {
        static::assertFalse($this->session->isStarted());

        // save dont work if no session is started.
        $this->session->save();

        $this->handler->shouldReceive('write')
            ->never();
    }

    public function testSessionHasSuspiciousFingerPrint(): void
    {
        $this->expectException(\Viserio\Component\Contract\Session\Exception\SuspiciousOperationException::class);

        $this->handler->shouldReceive('read')
            ->once()
            ->andReturn($this->getSessionInfoAsJsonString(0, 'foo'));

        $this->session->setId(self::SESSION_ID);
        $this->session->open();
    }

    public function testSessionReturnsFalseOnFirstTraceNull(): void
    {
        $this->handler->shouldReceive('read')
            ->once()
            ->andReturn('');
        $this->session->setId(self::SESSION_ID);

        static::assertFalse($this->session->open());
    }

    /**
     * @dataProvider getTestValidSessionName
     *
     * @var string
     *
     * @param string $sessionName
     */
    public function testValidSessionName(string $sessionName): void
    {
        $this->session->setName($sessionName);

        static::assertEquals($this->session->getName(), $sessionName);
    }

    public function getTestValidSessionName(): array
    {
        return [
            ['PHPSESSID'],
            ['a&b'],
            [',_-!@#$%^*(){}:<>/?'],
        ];
    }

    /**
     * @dataProvider getTestInvalidSessionName
     *
     * @var string
     *
     * @param string $sessionName
     */
    public function testInvalidSessionName(string $sessionName): void
    {
        $this->expectException(\Viserio\Component\Contract\Session\Exception\InvalidArgumentException::class);

        $this->session->setName($sessionName);
    }

    public function getTestInvalidSessionName(): array
    {
        return [
            ['a.b'],
            ['a['],
            ['a[]'],
            ['a[b]'],
            ['a=b'],
            ['a+b'],
        ];
    }

    public function testSessionMigration(): void
    {
        $this->session->start();

        $oldId = $this->session->getId();
        $this->handler->shouldReceive('destroy')
            ->never();

        static::assertTrue($this->session->migrate());
        static::assertNotEquals($oldId, $this->session->getId());

        $oldId = $this->session->getId();
        $this->handler->shouldReceive('destroy')
            ->once()
            ->with($oldId);

        static::assertTrue($this->session->migrate(true));
        static::assertNotEquals($oldId, $this->session->getId());
    }

    public function testCantSetInvalidId(): void
    {
        $this->session->setId('wrong');

        static::assertNotEquals('wrong', $this->session->getId());
    }

    public function testSessionInvalidate(): void
    {
        $this->session->start();
        $this->session->set('foo', 'bar');

        $oldId = $this->session->getId();

        static::assertGreaterThan(0, \count($this->session->getAll()));

        $this->handler->shouldReceive('destroy')
            ->once()
            ->with($oldId);

        static::assertTrue($this->session->invalidate());
        static::assertFalse($this->session->has('foo'));
        static::assertNotEquals($oldId, $this->session->getId());
        static::assertCount(0, $this->session->getAll());
    }

    public function testCanGetRequestsCount(): void
    {
        static::assertEquals(0, $this->session->getRequestsCount());

        $this->session->start();

        static::assertEquals(1, $this->session->getRequestsCount());
    }

    public function testSetMethodToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Session\Exception\SessionNotStartedException::class);
        $this->expectExceptionMessage('The session is not started.');

        $this->session->set('foo', 'bar');
    }

    public function testSetAndGetPreviousUrl(): void
    {
        $this->session->start();
        $this->session->setPreviousUrl('/test');

        static::assertSame('/test', $this->session->getPreviousUrl());
    }

    public function testStartMethodResetsLastTraceAndFirstTrace(): void
    {
        $this->session->setId(self::SESSION_ID);
        $this->handler->shouldReceive('read')
            ->once()
            ->andReturn($this->getSessionInfoAsJsonString(0, '', 1));

        static::assertTrue($this->session->isExpired());

        $this->session->open();

        $lastTrace  = $this->session->getLastTrace();
        $firstTrace = $this->session->getLastTrace();

        $this->session->start();

        static::assertFalse($this->session->isExpired());
        static::assertNotEquals($lastTrace, $this->session->getLastTrace());
        static::assertNotEquals($firstTrace, $this->session->getFirstTrace());
    }

    public function testStartMethodResetsRequestsCount(): void
    {
        $this->session->start();

        static::assertEquals(1, $this->session->getRequestsCount());
    }

    public function testStartMethodResetsIdRegenerationTrace(): void
    {
        $this->session->setId(self::SESSION_ID);
        $this->handler->shouldReceive('read')
            ->once()
            ->andReturn($this->getSessionInfoAsJsonString(0, '', 1));
        $this->session->open();

        $regenerationTrace = $this->session->getRegenerationTrace();

        $this->session->start();

        static::assertNotEquals($regenerationTrace, $this->session->getRegenerationTrace());
        static::assertGreaterThanOrEqual(Chronos::now()->getTimestamp() - 1, $this->session->getRegenerationTrace());
    }

    public function testStartMethodGeneratesFingerprint(): void
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => 'test']);

        $oldFingerprint = $this->session->getFingerprint();

        $this->session->addFingerprintGenerator(new UserAgentGenerator($request));
        $this->session->start();

        static::assertSame('', $oldFingerprint);
        static::assertEquals(40, \mb_strlen($this->session->getFingerprint()));
        static::assertNotEquals($oldFingerprint, $this->session->getFingerprint());
    }

    public function testStartMethodOpensSession(): void
    {
        $this->session->start();

        static::assertTrue($this->session->isStarted());
    }

    public function testRemove(): void
    {
        $this->session->start();
        $this->session->set('foo', 'bar');

        $pulled = $this->session->remove('foo');

        static::assertFalse($this->session->has('foo'));
        static::assertEquals('bar', $pulled);
    }

    public function testClear(): void
    {
        $this->session->start();
        $this->session->set('foo', 'bar');
        $this->session->clear();

        static::assertFalse($this->session->has('foo'));
    }

    public function testSessionIdShouldBeRegeneratedIfIdRequestsLimitReached(): void
    {
        $this->session->setIdRequestsLimit(3);
        $this->handler->shouldReceive('read')
            ->times(3)
            ->andReturn('');
        $this->handler->shouldReceive('write')
            ->times(3);
        $this->handler->shouldReceive('destroy')
            ->once();

        $this->session->start();
        $this->session->open();

        static::assertSame(1, $this->session->getRequestsCount());

        $this->session->save();

        static::assertTrue($this->session->open());

        static::assertSame(2, $this->session->getRequestsCount());

        $this->session->save();

        static::assertTrue($this->session->open());

        static::assertSame(3, $this->session->getRequestsCount());

        $this->session->save();
        // Session should migrate to a new one
        static::assertTrue($this->session->open());

        static::assertSame(1, $this->session->getRequestsCount());
    }

    public function testSessionIdShouldBeRegeneratedIfIdTtlLimitReached(): void
    {
        $this->session->setId(self::SESSION_ID);
        $this->handler->shouldReceive('read')
            ->twice()
            ->andReturn($this->getSessionInfoAsJsonString(0, '', 1));
        $this->session->setIdLiveTime(2);
        $this->handler->shouldReceive('write')
            ->times(1);
        $this->handler->shouldReceive('destroy')
            ->times(1);
        $this->session->open();

        static::assertSame(1, $this->session->getRequestsCount());
        static::assertSame(self::SESSION_ID, $this->session->getId());

        \sleep(3);

        $this->session->save();
        $this->session->open();

        static::assertNotSame(self::SESSION_ID, $this->session->getId());
    }

    public function testDataFlashing(): void
    {
        $this->session->start();
        $this->session->flash('foo', 'bar');
        $this->session->flash('bar', 0);

        static::assertTrue($this->session->has('foo'));
        static::assertEquals('bar', $this->session->get('foo'));
        static::assertEquals(0, $this->session->get('bar'));

        $this->session->ageFlashData();

        static::assertTrue($this->session->has('foo'));
        static::assertEquals('bar', $this->session->get('foo'));
        static::assertEquals(0, $this->session->get('bar'));

        $this->session->ageFlashData();

        static::assertFalse($this->session->has('foo'));
        static::assertNull($this->session->get('foo'));
    }

    public function testDataFlashingNow(): void
    {
        $this->session->start();
        $this->session->now('foo', 'bar');
        $this->session->now('bar', 0);

        static::assertTrue($this->session->has('foo'));
        static::assertEquals('bar', $this->session->get('foo'));
        static::assertEquals(0, $this->session->get('bar'));

        $this->session->ageFlashData();

        static::assertFalse($this->session->has('foo'));
        static::assertNull($this->session->get('foo'));
    }

    public function testDataMergeNewFlashes(): void
    {
        $this->session->start();
        $this->session->flash('foo', 'bar');
        $this->session->set('fu', 'baz');
        $this->session->set('_flash.old', ['qu']);

        static::assertNotFalse(\array_search('foo', $this->session->get('_flash.new'), true));
        static::assertFalse(\array_search('fu', $this->session->get('_flash.new'), true));

        $this->session->keep(['fu', 'qu']);

        static::assertNotFalse(\array_search('foo', $this->session->get('_flash.new'), true));
        static::assertNotFalse(\array_search('fu', $this->session->get('_flash.new'), true));
        static::assertNotFalse(\array_search('qu', $this->session->get('_flash.new'), true));
        static::assertFalse(\array_search('qu', $this->session->get('_flash.old'), true));
    }

    public function testReflash(): void
    {
        $this->session->start();
        $this->session->flash('foo', 'bar');
        $this->session->set('_flash.old', ['foo']);
        $this->session->reflash();

        $this->assertReflashNewAndOldFlashData();
    }

    public function testReflashWithNow(): void
    {
        $this->session->start();
        $this->session->now('foo', 'bar');
        $this->session->reflash();

        $this->assertReflashNewAndOldFlashData();
    }

    public function testIfSessionCanBeJsonSerialized(): void
    {
        static::assertSame([], $this->session->jsonSerialize());
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    /**
     * @param int    $requestsCount
     * @param string $fingerprint
     * @param int    $regenerationTrace
     *
     * @return string
     */
    private function getSessionInfoAsJsonString(int $requestsCount = 0, string $fingerprint = '', int $regenerationTrace = 0): string
    {
        return \json_encode(
            [
                'foo'          => 'bar',
                'bagged'       => ['name' => 'viserio'],
                '__metadata__' => [
                    'firstTrace'        => 0,
                    'lastTrace'         => 0,
                    'regenerationTrace' => $regenerationTrace,
                    'requestsCount'     => $requestsCount,
                    'fingerprint'       => $fingerprint,
                ],
            ],
            \JSON_PRESERVE_ZERO_FRACTION
        );
    }

    private function assertReflashNewAndOldFlashData(): void
    {
        $new = \array_flip($this->session->get('_flash.new'));
        $old = \array_flip($this->session->get('_flash.old'));

        static::assertTrue(isset($new['foo']));
        static::assertFalse(isset($old['foo']));
    }
}
