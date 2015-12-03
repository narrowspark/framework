<?php
namespace Viserio\Contracts\Mail;

/**
 * Mailer.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
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
     * @param string|array    $view
     * @param array           $data
     * @param \Closure|string $callback
     *
     * @return int
     */
    public function send($view, array $data, $callback);

    /**
     * Get the array of failed recipients.
     *
     * @return array
     */
    public function failures();
}
