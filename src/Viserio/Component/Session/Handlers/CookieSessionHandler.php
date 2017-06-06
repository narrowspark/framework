<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handlers;

use Cake\Chronos\Chronos;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;

class CookieSessionHandler implements SessionHandlerInterface
{
    /**
     * The cookie jar instance.
     *
     * @var \Viserio\Component\Contracts\Cookie\QueueingFactory
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
     * @param \Viserio\Component\Contracts\Cookie\QueueingFactory $cookie
     * @param int                                                 $lifetime The session lifetime in seconds
     */
    public function __construct(JarContract $cookie, int $lifetime)
    {
        $this->cookie   = $cookie;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId): string
    {
        $cookies = $this->request->getCookieParams();

        if (isset($cookies[$sessionId]) &&
            ! is_null($decoded = json_decode($cookies[$sessionId], true)) &&
            is_array($decoded)
        ) {
            if (isset($decoded['expires']) && Chronos::now()->getTimestamp() <= $decoded['expires']) {
                return $decoded['data'];
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data): bool
    {
        $this->cookie->queue(
            $sessionId,
            json_encode(
                [
                    'data'    => $data,
                    'expires' => Chronos::now()->addSeconds($this->lifetime)->getTimestamp(),
                ],
                \JSON_PRESERVE_ZERO_FRACTION
            ),
            $this->lifetime
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId): bool
    {
        $this->cookie->queue($this->cookie->delete($sessionId));

        return $this->cookie->hasQueued($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime): bool
    {
        return true;
    }

    /**
     * Set the request instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
}
