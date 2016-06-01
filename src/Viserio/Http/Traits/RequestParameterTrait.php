<?php
namespace Viserio\Http\Traits;

trait RequestParameterTrait
{
    /**
     * Return array or single key from $_GET.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function query(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query->all();
        }

        return $this->query->get($key, $default);
    }

    /**
     * Return array or single key from $_POST.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->request->all();
        }

        return $this->request->get($key, $default);
    }

    /**
     * Return array or single key from $_SERVER.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function server($key = null, $default = null)
    {
        if ($key === null) {
            return $this->server->all();
        }

        return $this->server->get($key, $default);
    }

    /**
     * Determine if the server has a key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasServer($key): bool
    {
        return $this->server->has($key);
    }

    /**
     * Return array or single key from $_FILES.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function files(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->files->all();
        }

        return $this->files->get($key, $default);
    }

    /**
     * Determine if the uploaded data contains a file.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasFiles($key): bool
    {
        return $this->files->has($key);
    }

    /**
     * Return array or single key from $_COOKIE.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function getCookie(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->cookies->all();
        }

        return $this->cookies->get($key, $default);
    }

    /**
     * Determine if a cookie is set on the request.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasCookie($key): bool
    {
        return $this->cookies->has($key);
    }

    /**
     * Return array or single key from headers taken from $_SERVER.
     *
     * @param string|null $key
     * @param string|null $default
     *
     * @return string
     */
    public function headers(string $key = null, $default = null): string
    {
        if ($key === null) {
            return $this->headers->all();
        }

        return $this->headers->get($key, $default);
    }

    /**
     * Determine if the header has a key.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasHeaders($key): bool
    {
        return $this->headers->has($key);
    }
}
