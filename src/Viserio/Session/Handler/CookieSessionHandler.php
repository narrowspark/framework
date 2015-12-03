<?php
namespace Viserio\Session\Handler;

use Viserio\Contracts\Cookie\Factory as CookieContract;
use Viserio\Contracts\Http\Request as RequestContract;

class CookieSessionHandler implements \SessionHandlerInterface
{
    /**
     * The cookie jar instance.
     *
     * @var CookieContract
     */
    protected $cookie;

    /**
     * The request instance.
     *
     * @var RequestContract
     */
    protected $request;

    /**
     * The time the cookie expires.
     *
     * @var int
     */
    protected $minutes;

    /**
     * Create a new cookie driven handler instance.
     *
     * @param CookieContract $cookie
     * @param int            $minutes
     */
    public function __construct(CookieContract $cookie, $minutes)
    {
        $this->cookie = $cookie;
        $this->minutes = $minutes;
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId)
    {
        return $this->request->getCookie($sessionId) ?: '';
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        //TODO
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId)
    {
        $this->cookie->remove($sessionId);
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        return true;
    }

    /**
     * Set the request instance.
     *
     * @param RequestContract $request
     */
    public function setRequest(RequestContract $request)
    {
        $this->request = $request;
    }
}
