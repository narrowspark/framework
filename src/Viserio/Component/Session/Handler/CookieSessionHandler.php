<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handler;

use Cake\Chronos\Chronos;
use Psr\Http\Message\ServerRequestInterface;
use SessionHandlerInterface;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;

class CookieSessionHandler implements SessionHandlerInterface
{
    /**
     * The cookie jar instance.
     *
     * @var JarContract
     */
    protected $cookie;

    /**
     * The request instance.
     *
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $lifetime;

    /**
     * Create a new cookie driven handler instance.
     *
     * @param JarContract $cookie
     * @param int         $lifetime
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
    public function read($sessionId)
    {
        $cookies = $this->request->getCookieParams();

        if (! is_null($decoded = json_decode($cookies, true)) && is_array($decoded)) {
            if (isset($decoded[$sessionId])) {
                $data = $decoded[$sessionId];

                if (isset($data['expires']) && Chronos::now()->getTimestamp() <= $data['expires']) {
                    return $data['data'];
                }
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $this->cookie->queue(
            $sessionId,
            json_encode(
                [
                    'data'    => $data,
                    'expires' => Chronos::now()->addMinutes($this->lifetime)->getTimestamp(),
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
    public function destroy($sessionId)
    {
        $this->cookie->queue($this->cookie->delete($sessionId));

        return $this->cookie->hasQueued($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Set the request instance.
     *
     * @param ServerRequestInterface $request
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }
}
