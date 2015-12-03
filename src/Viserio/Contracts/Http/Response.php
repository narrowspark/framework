<?php
namespace Viserio\Contracts\Http;

/**
 * Response.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Response
{
    /**
     * Send HTTP headers and body.
     *
     * @return \Viserio\Http\Response
     */
    public function send();

    /**
     * Set the content on the response.
     *
     * @param mixed $content
     *
     * @return \Viserio\Http\Response
     */
    public function setContent($content);
}
