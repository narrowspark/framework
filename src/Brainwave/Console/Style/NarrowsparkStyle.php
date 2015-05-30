<?php

namespace Brainwave\Console\Style;

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

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * NarrowsparkStyle.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.7-dev
 */
class NarrowsparkStyle extends SymfonyStyle
{
    /**
     * Formats an error result bar.
     *
     * @param string|array $message
     */
    public function error($message)
    {
        $this->block($message, null, 'error', ' ', false);
    }
}
