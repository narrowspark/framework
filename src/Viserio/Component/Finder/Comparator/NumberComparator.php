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

use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * NumberComparator compiles a simple comparison to an anonymous
 * subroutine, which you can call with a value to be tested again.
 *
 * Now this would be very pointless, if NumberCompare didn't understand
 * magnitudes.
 *
 * The target value may use magnitudes of kilobytes (k, ki),
 * megabytes (m, mi), or gigabytes (g, gi).  Those suffixed
 * with an i use the appropriate 2**n version in accordance with the
 * IEC standard: http://physics.nist.gov/cuu/Units/binary.html
 *
 * Based on the Perl Number::Compare module.
 *
 * Based on the symfony finder package.
 *
 * @see https://raw.githubusercontent.com/tomzx/finder/master/src/Finder/Comparator/NumberComparator.php
 *
 * @author    Fabien Potencier <fabien@symfony.com> PHP port
 * @author    Richard Clamp <richardc@unixbeard.net> Perl version
 * @copyright 2004-2005 Fabien Potencier <fabien@symfony.com>
 * @copyright 2002 Richard Clamp <richardc@unixbeard.net>
 *
 * @see http://physics.nist.gov/cuu/Units/binary.html
 */
class NumberComparator extends Comparator
{
    /**
     * Create a new NumberComparator instance.
     *
     * @param int|string $test A comparison string or an integer
     *
     * @throws \Viserio\Contract\Finder\Exception\InvalidArgumentException If the test is not understood
     */
    public function __construct($test)
    {
        if (\preg_match('#^\s*(==|!=|[equal ?|less ?|greater ?]+<=>|[<>]=?)?\s*([0-9\.]+)\s*([kmg]i?)?\s*$#i', (string) $test, $matches) !== 1) {
            throw new InvalidArgumentException(\sprintf('Don\'t understand [%s] as a number test.', (string) $test));
        }

        $target = $matches[2];

        if (! \is_numeric($target)) {
            throw new InvalidArgumentException(\sprintf('Invalid number [%s].', $target));
        }

        if (isset($matches[3])) {
            // magnitude
            switch (\strtolower($matches[3])) {
                case 'k':
                    $target *= 1000;

                    break;
                case 'ki':
                    $target *= 1024;

                    break;
                case 'm':
                    $target *= 1000000;

                    break;
                case 'mi':
                    $target *= 1024 * 1024;

                    break;
                case 'g':
                    $target *= 1000000000;

                    break;
                case 'gi':
                    $target *= 1024 * 1024 * 1024;

                    break;
            }
        }

        $this->setTarget($target);
        $this->setOperator($matches[1] ?? '==');
    }
}
