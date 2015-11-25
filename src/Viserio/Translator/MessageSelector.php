<?php
namespace Viserio\Translator;

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
 * @version     0.10.0
 */

use Viserio\Translator\Traits\IntervalTrait;

/**
 * MessageSelector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class MessageSelector
{
    use IntervalTrait;

    /**
     * PluralizationRules instance.
     *
     * @var \Viserio\Translator\PluralizationRules
     */
    protected $pluralization;

    /**
     * Set pluralization.
     *
     * @param \Viserio\Translator\PluralizationRules $pluralization
     */
    public function setPluralization(PluralizationRules $pluralization)
    {
        $this->pluralization = $pluralization;
    }

    /**
     * Get pluralization.
     *
     * @return \Viserio\Translator\PluralizationRules
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
     *
     * @api
     */
    public function choose($message, $number, $locale)
    {
        $parts = explode('|', $message);

        $explicitRules = [];
        $standardRules = [];

        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^(?P<interval>'.$this->getIntervalRegexp().')\s*(?P<message>.*?)$/x', $part, $matches)) {
                $explicitRules[$matches['interval']] = $matches['message'];
            } elseif (preg_match('/^\w+\:\s*(.*?)$/', $part, $matches)) {
                $standardRules[] = $matches[1];
            } else {
                $standardRules[] = $part;
            }
        }

        // try to match an explicit rule, then fallback to the standard ones
        foreach ($explicitRules as $interval => $m) {
            if ($this->test($number, $interval)) {
                return $m;
            }
        }

        $pluralization = $this->getPluralization();

        $position = $pluralization->get($number, $locale);

        if (!isset($standardRules[$position])) {
            // when there's exactly one rule given, and that rule is a standard
            // rule, use this rule
            if (1 === count($parts) && isset($standardRules[0])) {
                return $standardRules[0];
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Unable to choose a translation for "%s" with locale "%s" for value "%d". Double check that this translation has the correct plural options (e.g. "There is one apple|There are %%count%% apples").',
                    $message,
                    $locale,
                    $number
                )
            );
        }

        return $standardRules[$position];
    }
}
