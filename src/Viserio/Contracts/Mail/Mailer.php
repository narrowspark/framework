<?php
namespace Viserio\Contracts\Mail;

use Closure;

interface Mailer
{
    /**
     * Send a new message when only a raw text part.
     *
     * @param string          $text
     * @param \Closure|string $callback
     *
     * @return int
     */
    public function raw($text, $callback);

    /**
     * Send a new message using a view.
     *
     * @param string|array $view
     * @param array        $data
     * @param \Closure     $callback
     *
     * @return int
     */
    public function send($view, array $data, Closure $callback);

    /**
     * Get the array of failed recipients.
     *
     * @return array
     */
    public function failures();
}
