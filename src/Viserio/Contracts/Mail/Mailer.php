<?php
declare(strict_types=1);
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
    public function raw(string $text, $callback): int;

    /**
     * Send a new message using a view.
     *
     * @param string|array $view
     * @param array        $data
     * @param \Closure     $callback
     *
     * @return int
     */
    public function send($view, array $data, Closure $callback): int;

    /**
     * Get the array of failed recipients.
     *
     * @return array
     */
    public function failures(): array;
}
