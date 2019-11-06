<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Session\Handler;

use Cake\Chronos\Chronos;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contract\Cookie\QueueingFactory as JarContract;

class CookieSessionHandler extends AbstractSessionHandler
{
    /**
     * The cookie jar instance.
     *
     * @var \Viserio\Contract\Cookie\QueueingFactory
     */
    protected $cookie;

    /**
     * The request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * The number of seconds the session should be valid.
     *
     * @var int
     */
    protected $lifetime;

    /**
     * Create a new cookie driven handler instance.
     *
     * @param \Viserio\Contract\Cookie\QueueingFactory $cookie
     * @param int                                      $lifetime The session lifetime in seconds
     */
    public function __construct(JarContract $cookie, int $lifetime)
    {
        $this->cookie = $cookie;
        $this->lifetime = $lifetime;
    }

    /**
     * Set the request instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Cleanup old sessions.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     *
     * @param int $maxlifetime
     *
     * @return bool
     */
    public function gc($maxlifetime): bool
    {
        return true;
    }

    /**
     * Update timestamp of a session.
     *
     * @param string $sessionId   The session id
     * @param string $sessionData
     *
     * @return bool
     */
    public function updateTimestamp($sessionId, $sessionData): bool
    {
        $cookies = $this->cookie->getQueuedCookies();
        $cookie = $cookies[$sessionId] ?? null;

        if ($cookie === null) {
            return false;
        }

        $this->cookie->queue($this->cookie->delete($sessionId));
        $this->cookie->queue(
            $cookie->withExpires(
                Chronos::now()->addSeconds($this->lifetime)->getTimestamp()
            )
        );

        return $this->cookie->hasQueued($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead($sessionId): string
    {
        $cookies = $this->request->getCookieParams();

        if (! isset($cookies[$sessionId])) {
            return '';
        }

        $decoded = \json_decode(\base64_decode($cookies[$sessionId], true), true);

        if (\is_array($decoded)
            && (isset($decoded['expires']) && Chronos::now()->getTimestamp() <= $decoded['expires'])
        ) {
            return $decoded['data'];
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($sessionId, $data): bool
    {
        $this->cookie->queue(
            $sessionId,
            \base64_encode(\json_encode(
                [
                    'data' => $data,
                    'expires' => Chronos::now()->addSeconds($this->lifetime)->getTimestamp(),
                ],
                \JSON_PRESERVE_ZERO_FRACTION
            )),
            $this->lifetime
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy($sessionId): bool
    {
        $this->cookie->queue($this->cookie->delete($sessionId));

        return $this->cookie->hasQueued($sessionId);
    }
}
