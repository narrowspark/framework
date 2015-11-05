<?php
namespace Brainwave\Translator\Traits;

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
 * @version     0.10.0-dev
 */

/**
 * IntervalTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
trait IntervalTrait
{
    /**
     * Tests if the given number is in the math interval.
     *
     * @param int    $number   A number
     * @param string $interval An interval
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function test($number, $interval)
    {
        $interval = trim($interval);

        if (!preg_match('/^'.$this->getIntervalRegexp().'$/x', $interval, $matches)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid interval.', $interval));
        }

        if ($matches[1]) {
            foreach (explode(',', $matches[2]) as $n) {
                if ($number === $n) {
                    return true;
                }
            }
        } else {
            $leftNumber = $this->convertNumber($matches['left']);
            $rightNumber = $this->convertNumber($matches['right']);

            return
                ('[' === $matches['left_delimiter'] ? $number >= $leftNumber : $number > $leftNumber)
                && (']' === $matches['right_delimiter'] ? $number <= $rightNumber : $number < $rightNumber)
            ;
        }

        return false;
    }

    /**
     * Returns a Regexp that matches valid intervals.
     *
     * @return string A Regexp (without the delimiters)
     */
    public function getIntervalRegexp()
    {
        return <<<EOF
        ({\s*
            (\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)
        \s*})
            |
        (?P<left_delimiter>[\[\]])
            \s*
            (?P<left>-Inf|\-?\d+(\.\d+)?)
            \s*,\s*
            (?P<right>\+?Inf|\-?\d+(\.\d+)?)
            \s*
        (?P<right_delimiter>[\[\]])
EOF;
    }

    /**
     * Convert number.
     *
     * @param int $number
     *
     * @return double
     */
    protected function convertNumber($number)
    {
        if ('-Inf' == $number) {
            return log(0);
        } elseif ('+Inf' == $number || 'Inf' == $number) {
            return -log(0);
        }

        return (float) $number;
    }
}
