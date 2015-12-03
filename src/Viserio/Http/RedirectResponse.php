<?php
namespace Viserio\Http;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Viserio\Session\Store as SessionStore;
use Viserio\Support\Str;

/**
 * RedirectResponse.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class RedirectResponse extends SymfonyRedirectResponse
{
    /**
     * The request instance.
     *
     * @var \Viserio\Http\Request
     */
    protected $request;

    /**
     * The session store implementation.
     *
     * @var \Viserio\Session\Store
     */
    protected $session;

    /**
     * Set a header on the Response.
     *
     * @param string $key
     * @param string $value
     * @param bool   $replace
     *
     * @return $this
     */
    public function header($key, $value, $replace = true)
    {
        $this->headers->set($key, $value, $replace);

        return $this;
    }

    /**
     * Flash a piece of data to the session.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return \Viserio\Http\RedirectResponse
     */
    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];

        foreach ($key as $k => $v) {
            $this->session->flash($k, $v);
        }

        return $this;
    }

    /**
     * Add a cookie to the response.
     *
     * @param \Symfony\Component\HttpFoundation\Cookie $cookie
     *
     * @return $this
     */
    public function withCookie(Cookie $cookie)
    {
        $this->headers->setCookie($cookie);

        return $this;
    }

    /**
     * Add multiple cookies to the response.
     *
     * @param array $cookies
     *
     * @return $this
     */
    public function withCookies(array $cookies)
    {
        foreach ($cookies as $cookie) {
            $this->headers->setCookie($cookie);
        }

        return $this;
    }

    /**
     * Flash an array of input to the session.
     *
     * @param array|null $input
     *
     * @return $this
     */
    public function withInput(array $input = null)
    {
        $input = $input ?: $this->request->input();
        $this->session->flashInput(array_filter($input, function ($value) {
            return !$value instanceof SymfonyUploadedFile;
        }));

        return $this;
    }

    /**
     * Flash an array of input to the session.
     *
     * @param  mixed string
     *
     * @return $this
     */
    public function onlyInput()
    {
        return $this->withInput($this->request->only(func_get_args()));
    }

    /**
     * Flash an array of input to the session.
     *
     * @param  mixed string
     *
     * @return \Viserio\Http\RedirectResponse
     */
    public function exceptInput()
    {
        return $this->withInput($this->request->except(func_get_args()));
    }

    /**
     * Get the request instance.
     *
     * @return \Viserio\Http\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the request instance.
     *
     * @param \Viserio\Http\Request $request
     *
     * @return \Viserio\Http\RedirectResponse|null
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the session store implementation.
     *
     * @return \Viserio\Session\Store
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set the session store implementation.
     *
     * @param \Viserio\Session\Store $session
     */
    public function setSession(SessionStore $session)
    {
        $this->session = $session;
    }

    /**
     * Dynamically bind flash data in the session.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @throws \BadMethodCallException
     *
     * @return RedirectResponse
     */
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::snake(substr($method, 4)), $parameters[0]);
        }

        throw new \BadMethodCallException(sprintf('Method [%s] does not exist on Redirect.', $method));
    }
}
