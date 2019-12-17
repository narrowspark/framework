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

namespace Viserio\Component\Filesystem;

final class Permissions
{
    /**
     * @codeCoverageIgnore
     *
     * Private constructor; non-instantiable.
     */
    private function __construct()
    {
    }

    /**
     * Converts mode from string representation to decimal if necessary.
     *
     * This adds a utility function that converts mode strings to correct represntations.
     *
     * Permissions::notation(0755) and Permissions::notation(1755) just passes it through.
     * Permissions::notation('0755') and Permissions::notation('1755') assumes the chars to be octets and sets mode to rwxr-xr-x and rwxr-xr-t respectively - that works according to naive scenario. Permissions::notation('01755') works as well.
     * Permissions::notation('rwxr-xr-x') sets mode to rwxr-xr-x. Adding d or placing guid, suid and sticky bits also work as expected, both with executable bit on or off.
     *
     * @param float|int|string $modeString
     *
     * @return float|int
     */
    public static function notation($modeString)
    {
        // we don't process it if it's not a string
        if (! \is_string($modeString)) {
            return $modeString;
        }

        // this takes care of '755', '0755', '1755', '01755'
        if (\is_numeric($modeString)) {
            return \octdec($modeString);
        }

        // this point is reached in case of something like 'drwSrwxr-T'
        // here we'll store the three usual octets for start
        $mode = 0;
        // this will hold the first octet; we'll merge it in $mode at the end
        $special = 0;

        // let's go through the string char by char
        for ($i = 0, $iMax = \strlen($modeString); $i < $iMax; $i++) {
            // for each iteration we shift the mode by one bit
            // thus putting a 0 at the end
            $mode <<= 1;

            // we must also shift the special bits once every three chars
            // works for both drwxrwxrwt and rwxrwxrwt
            if (0 === $i % 3) {
                $special = $special << 1;
            }

            $char = $modeString[$i];

            // the special bit is set on these letters taking the place of execution bit
            // their order is fixed so it's enough to check if we got any of those
            if (\in_array($char, ['S', 's', 'T', 't'], true)) {
                $special |= 1;
            }

            // these letters are the only that don't set the corresponding bit in mode
            if (\in_array($char, ['-', 'S', 'T', 'd'], true)) {
                continue;
            }
            // if we reached this, set the bit on
            $mode |= 1;
        }

        // finally shift the special bits so they come first and return everything
        return $mode |= ($special << 9);
    }
}
