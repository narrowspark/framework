<?php
declare(strict_types=1);
namespace Viserio\Component\Translation;

use InvalidArgumentException;
use Viserio\Component\Contracts\Translation\MessageSelector as MessageSelectorContract;
use Viserio\Component\Contracts\Translation\PluralizationRules as PluralizationRulesContract;
use Viserio\Component\Translation\Traits\IntervalTrait;

class MessageSelector implements MessageSelectorContract
{
    use IntervalTrait;

    /**
     * PluralizationRules instance.
     *
     * @var \Viserio\Component\Contracts\Translation\PluralizationRules
     */
    protected $pluralization;

    /**
     * {@inheritdoc}
     */
    public function setPluralization(PluralizationRulesContract $pluralization): MessageSelectorContract
    {
        $this->pluralization = $pluralization;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPluralization(): PluralizationRulesContract
    {
        return $this->pluralization;
    }

    /**
     * {@inheritdoc}
     */
    public function choose(string $message, $number, string $locale): string
    {
        $parts = explode('|', $message);

        $explicitRules = $this->getExplicitRules($parts);
        $standardRules = $this->getStandardRules($parts);

        // try to match an explicit rule, then fallback to the standard ones
        if (! empty($explicitRules)) {
            foreach ($explicitRules as $interval => $string) {
                if ($this->intervalTest($number, $interval)) {
                    return $string;
                }
            }
        }

        $position = $this->pluralization->get((int) $number, $locale);

        if (! isset($standardRules[$position])) {
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
    private function getExplicitRules(array $parts): array
    {
        $explicitRules = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match(
                '/^(?P<interval>' . $this->getIntervalRegexp() . ')\s*(?P<message>.*?)$/x',
                $part,
                $matches
            )) {
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
    private function getStandardRules(array $parts): array
    {
        $standardRules = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if (preg_match('/^\w+\:\s*(.*?)$/', $part, $matches)) {
                $standardRules[] = $matches[1];
            } elseif (! preg_match(
                '/^(?P<interval>' . $this->getIntervalRegexp() . ')\s*(?P<message>.*?)$/x',
                $part
            )) {
                $standardRules[] = $part;
            }
        }

        return $standardRules;
    }
}
