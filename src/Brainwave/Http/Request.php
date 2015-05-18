<?php

namespace Brainwave\Http;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Http\Request as RequestContract;
use Brainwave\Http\Traits\RequestParameterTrait;
use Brainwave\Support\Arr;
use Brainwave\Support\Str;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Request.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Request extends SymfonyRequest implements RequestContract, \ArrayAccess
{
    /*
     * Parameter encapsulation
     */
    use RequestParameterTrait;

    /**
     * The decoded JSON content for the request.
     *
     * @var string|ParameterBag
     */
    protected $json;

    /**
     * Get the JSON payload for the request.
     *
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (!isset($this->json)) {
            $this->json = new ParameterBag((array) json_decode($this->getContent(), true));
        }

        if (null === $key) {
            return $this->json;
        }

        return Arr::get($this->json->all(), $key, $default);
    }

    /**
     * Merge new input into the current request's input array.
     *
     * @param array $input
     */
    public function merge(array $input)
    {
        $this->getInputSource()->add($input);
    }

    /**
     * Replace the input for the current request.
     *
     * @param array $input
     */
    public function replace(array $input)
    {
        $this->getInputSource()->replace($input);
    }

    /**
     * {@inheritdoc}
     */
    public function uriSegment($index, $default = null)
    {
        $uri = trim($this->getPathInfo(), '/');
        $segments = explode('/', $uri);

        return (isset($segments[$index - 1])) ? $segments[$index - 1] : $default;
    }

    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function root()
    {
        return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function fullUrl()
    {
        $query = $this->getQueryString();

        return $query ? $this->url().'?'.$query : $this->url();
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern === '' ? '/' : $pattern;
    }

    /**
     * Get the current encoded path info for the request.
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    /**
     * Get a segment from the URI (1 based index).
     *
     * @param string $index
     * @param mixed  $default
     *
     * @return string
     */
    public function segment($index, $default = null)
    {
        return Arr::get($this->segments(), $index - 1, $default);
    }

    /**
     * Get all of the segments for the request path.
     *
     * @return array
     */
    public function segments()
    {
        $segments = explode('/', $this->path());

        return array_values(
            array_filter(
                $segments,
                function ($v) {
                    return $v !== '';
                }
            )
        );
    }

    /**
     * Return the Request instance.
     *
     * @return $this
     */
    public function instance()
    {
        return $this;
    }

    /**
     * Determine if the request is the result of an AJAX call.
     *
     * @return bool
     */
    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Determine if the request is the result of an PJAX call.
     *
     * @return bool
     */
    public function isPjax()
    {
        return $this->headers->get('X-PJAX') === true;
    }

    /**
     * Determine if the request is sending JSON.
     *
     * @return bool
     */
    public function isJson()
    {
        return Str::contains($this->headers('CONTENT_TYPE'), '/json');
    }

    /**
     * Determine if the current request is asking for JSON in return.
     *
     * @return bool
     */
    public function wantsJson()
    {
        $acceptable = $this->getAcceptableContentTypes();

        return isset($acceptable[0]) && 'application/json' === $acceptable[0];
    }

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @param  mixed string
     *
     * @return bool
     */
    public function is()
    {
        foreach (func_get_args() as $pattern) {
            if (Str::is($pattern, urldecode($this->path()))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve an input item from the request.
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return string
     */
    public function input($key = null, $default = null)
    {
        $input = $this->getInputSource()->all() + $this->query->all();

        return Arr::get($input, $key, $default);
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all()
    {
        return array_replace_recursive($this->input(), $this->files->all());
    }

    /**
     * Determine if the request contains a given input item key.
     *
     * @param string|array $key
     *
     * @return bool
     */
    public function exists($key)
    {
        $keys = is_array($key) ? $key : func_get_args();
        $input = $this->all();

        foreach ($keys as $value) {
            if (!array_key_exists($value, $input)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the request contains a non-empty value for an input item matching a given pattern.
     *
     * @param string $pattern
     *
     * @return bool
     */
    public function hasRegex($pattern)
    {
        foreach ($this->all() as $key => $value) {
            if (!$this->isEmptyString($key) && preg_match($pattern, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the request contains a non-empty value for an input item.
     *
     * @param string|array $key
     *
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if ($this->isEmptyString($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create an Brainwave request from a Symfony instance.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Brainwave\Http\Request
     */
    public static function createFromBase(SymfonyRequest $request)
    {
        if ($request instanceof static) {
            return $request;
        }

        $content = $request->content;

        $request = (new static())->duplicate(
            $request->query->all(),
            $request->request->all(),
            $request->attributes->all(),
            $request->cookies->all(),
            $request->files->all(),
            $request->server->all()
        );

        $request->content = $content;
        $request->request = $request->getInputSource();

        return $request;
    }

    /**
     * Get a subset of the items from the input data.
     *
     * @param array $keys
     *
     * @return array
     */
    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $results = [];
        $input = $this->all();

        foreach ($keys as $key) {
            $value = Arr::get($input, $key);

            if (!is_null($value)) {
                Arr::set($results, $key, $value);
            }
        }

        return $results;
    }

    /**
     * Get all of the input except for a specified array of items.
     *
     * @param array $keys
     *
     * @return array
     */
    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        $results = $this->all();
        Arr::forget($results, $keys);

        return $results;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->all());
    }

    /**
     * Get the value at the given offset.
     *
     * @param string $offset
     *
     * @return string
     */
    public function offsetGet($offset)
    {
        return $this->all()[$offset];
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value)
    {
        $this->getInputSource()->set($offset, $value);
    }

    /**
     * Remove the value at the given offset.
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        $this->getInputSource()->remove($offset);
    }

    /**
     * Get an input element from the request.
     *
     * @param string $key
     *
     * @return string|null
     */
    public function __get($key)
    {
        $input = $this->input();

        if (array_key_exists($key, $input)) {
            return $this->input($key);
        }

        return;
    }

    /**
     * Get the input source for the request.
     *
     * @return \Symfony\Component\HttpFoundation\ParameterBag
     */
    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return 'GET' === $this->getMethod() ? $this->query : $this->request;
    }

    /**
     * Determine if the given input key is an empty string for "has".
     *
     * @param string $key
     *
     * @return bool
     */
    protected function isEmptyString($key)
    {
        $boolOrArray = is_bool($this->input($key)) || is_array($this->input($key));

        return !$boolOrArray && trim((string) $this->input($key)) === '';
    }
}
