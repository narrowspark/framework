<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Translation;

interface MessageFormatter
{
    /**
     * Formats a localized message pattern with given arguments.
     *
     * @param string $message    The message (may also be an object that can be cast to string)
     * @param string $locale     The message locale
     * @param array  $parameters An array of parameters for the message
     *
     * @return string
     */
    public function format(string $message, string $locale, array $parameters = []): string;
}
