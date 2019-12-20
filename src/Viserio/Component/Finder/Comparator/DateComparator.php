<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Finder\Comparator;

use DateTime;
use Exception;
use Viserio\Contract\Finder\Comparator\DateComparator as DateComparatorContract;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * DateCompare compiles date comparisons.
 *
 * Based on the symfony finder package
 *
 * @see https://raw.githubusercontent.com/tomzx/finder/master/src/Finder/Comparator/DateComparator.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DateComparator extends Comparator implements DateComparatorContract
{
    /** @var string */
    private $timeType;

    /**
     * @param string $test     A comparison string
     * @param string $timeType
     *
     * @throws \Viserio\Contract\Finder\Exception\InvalidArgumentException If the test is not understood
     */
    public function __construct(string $test, string $timeType = self::LAST_MODIFIED)
    {
        if (! \preg_match('#^\s*(==|!=|[<>]=?|after|since|before|until)?\s*(.+?)\s*$#i', $test, $matches)) {
            throw new InvalidArgumentException(\sprintf('Don\'t understand [%s] as a date test.', $test));
        }

        try {
            $date = new DateTime($matches[2]);
            $target = $date->format('U');
        } catch (Exception $e) {
            throw new InvalidArgumentException(\sprintf('[%s] is not a valid date.', $matches[2]));
        }

        $operator = $matches[1] ?? '==';

        if ('since' === $operator || 'after' === $operator) {
            $operator = '>';
        }

        if ('until' === $operator || 'before' === $operator) {
            $operator = '<';
        }

        $this->timeType = $timeType;
        $this->setOperator($operator);
        $this->setTarget($target);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeType(): string
    {
        return $this->timeType;
    }
}
