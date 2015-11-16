<?php
namespace Viserio\Http;

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
 * @version     0.10.0-dev
 */

use Viserio\Contracts\Http\Response as ResponseContract;
use Viserio\Contracts\Support\Jsonable;
use Viserio\Contracts\Support\Renderable;
use Viserio\Http\Traits\ResponseParameterTrait;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Response.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class Response extends SymfonyResponse implements ResponseContract
{
    /*
     * Parameter encapsulation
     */
    use ResponseParameterTrait;

    /**
     * The original content of the response.
     *
     * @var mixed
     */
    public $original;

    /**
     * Set the content on the response.
     *
     * @param mixed $content
     *
     * @return \Viserio\Http\Response
     */
    public function setContent($content)
    {
        $this->original = $content;

        // If the content is "JSONable" we will set the appropriate header and convert
        // the content to JSON. This is useful when returning something like models
        // from routes that will be automatically transformed to their JSON form.
        if ($this->shouldBeJson($content)) {
            $this->headers->set('Content-Type', 'application/json');
            $content = $this->morphToJson($content);
        } elseif ($content instanceof Renderable) {
            // If this content implements the "Renderable" interface then we will call the
            // render method on the object so we will avoid any "__toString" exceptions
            // that might be thrown and have their errors obscured by PHP's handling.
            $content = $content->render();
        }

        return parent::setContent($content);
    }

    /**
     * Get the original response content.
     *
     * @return mixed
     */
    public function getOriginalContent()
    {
        return $this->original;
    }

    /**
     * Morph the given content into JSON.
     *
     * @param mixed $content
     *
     * @return string
     */
    protected function morphToJson($content)
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        }

        return json_encode($content);
    }

    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param mixed $content
     *
     * @return bool
     */
    protected function shouldBeJson($content)
    {
        return $content instanceof Jsonable ||
               $content instanceof \ArrayObject ||
               is_array($content);
    }
}
