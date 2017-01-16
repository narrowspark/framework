<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Translation;

interface MessageSelector
{
    /**
     * Set pluralization.
     *
     * @param \Viserio\Component\Contracts\Translation\PluralizationRules $pluralization
     *
     * @return $this
     */
    public function setPluralization(PluralizationRules $pluralization): MessageSelector;

    /**
     * Get pluralization.
     *
     * @return \Viserio\Component\Contracts\Translation\PluralizationRules
     */
    public function getPluralization(): PluralizationRules;

    /**
     * Given a message with different plural translations separated by a
     * pipe (|), this method returns the correct portion of the message based
     * on the given number, locale and the pluralization rules in the message
     * itself.
     *
     * The message supports two different types of pluralization rules:
     *
     * interval: {0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples
     * indexed:  There is one apple|There are %count% apples
     *
     * The indexed solution can also contain labels (e.g. one: There is one apple).
     * This is purely for making the translations more clear - it does not
     * affect the functionality.
     *
     * The two methods can also be mixed:
     *     {0} There are no apples|one: There is one apple|more: There are %count% apples
     *
     * @param string    $message The message being translated
     * @param int|float $number  The number of items represented for the message
     * @param string    $locale  The locale to use for choosing
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function choose(string $message, $number, string $locale): string;
}
