<?php

namespace Brainwave\Contracts\Translator;

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
 * @version     0.9.8-dev
 */

/**
 * PluralCategory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
interface PluralCategory
{
    /**
     * Returns category key by count.
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count);
}
