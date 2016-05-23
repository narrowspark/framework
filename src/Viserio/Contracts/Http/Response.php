<?php
namespace Viserio\Contracts\Http;

interface Response
{
    /**
     * Send HTTP headers and body.
     *
     * @return \Viserio\Http\Response
     */
    public function send(): \Viserio\Http\Response;

    /**
     * Set the content on the response.
     *
     * @param mixed $content
     *
     * @return \Viserio\Http\Response
     */
    public function setContent($content): \Viserio\Http\Response;
}
