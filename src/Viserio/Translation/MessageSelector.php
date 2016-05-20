<?php
namespace Viserio\Translation;

use InvalidArgumentException;
use Viserio\Translation\Traits\IntervalTrait;

class MessageSelector
{
    use IntervalTrait;

    /**
     * PluralizationRules instance.
     *
     * @var \Viserio\Translation\PluralizationRules
     */
    protected $pluralization;

    /**
     * Set pluralization.
     *
     * @param \Viserio\Translation\PluralizationRules $pluralization
     */
    public function setPluralization(PluralizationRules $pluralization)
    {
        $this->pluralization = $pluralization;
    }

    /**
     * Get pluralization.
     *
     * @return \Viserio\Translation\PluralizationRules
     */
    public function getPluralization()
    {
        return $this->pluralization;
    }

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
     * @param string $message The message being translated
     * @param int    $number  The number of items represented for the message
     * @param string $locale  The locale to use for choosing
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function choose($message, $number, $locale)
    {
        $parts = explode('|', $message);

        $explicitRules = $this->getExplicitRules($parts);
        $standardRules = $this->getStandardRules($parts);

        // try to match an explicit rule, then fallback to the standard ones
        if (!empty($explicitRules)) {
            foreach ($explicitRules as $interval => $string) {
                if ($this->intervalTest($number, $interval)) {
                    return $string;
                }
            }
        }

        $position = $this->pluralization->get($number, $locale);

        // var_dump($number, $position, $standardRules);

        if (!isset($standardRules[$position])) {
            // when there's exactly one rule given, and that rule is a standard
            // rule, use this rule
            if (count($parts) === 1 && isset($standardRules[0])) {
                return $standardRules[0];
            }

            throw new InvalidArgumentException(
                sprintf(
                    'Unable to choose a translation for "%s" with locale "%s" for value "%d".' .
                    ' Double check that this translation has the correct plural options' .
                    '(e.g. "There is one apple|There are %%count%% apples").',
                    $message,
                    $locale,
                    $number
                )
            );
        }

        return $standardRules[$position];
    }

    /**
     * Get explicit rules for sting.
     *
     * @param array $parts
     *
     * @return array
     */
    private function getExplicitRules(array $parts)
    {
        $explicitRules = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if (
                preg_match('/^(?P<interval>' . $this->getIntervalRegexp() . ')\s*(?P<message>.*?)$/x', $part, $matches)
            ) {
                $explicitRules[$matches['interval']] = $matches['message'];
            }
        }

        return $explicitRules;
    }

    /**
     * Get standard rules for sting.
     *
     * @param array $parts
     *
     * @return array
     */
    private function getStandardRules(array $parts)
    {
        $standardRules = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match('/^\w+\:\s*(.*?)$/', $part, $matches)) {
                $standardRules[] = $matches[1];
            } elseif (
                !preg_match('/^(?P<interval>' . $this->getIntervalRegexp() . ')\s*(?P<message>.*?)$/x', $part)
            ) {
                $standardRules[] = $part;
            }
        }

        return $standardRules;
    }
}
