<?php

namespace Brainwave\Console\Input;

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

use Symfony\Component\Console\Input\InputArgument as SymfonyInputArgument;

/**
 * InputArgument.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class InputArgument extends SymfonyInputArgument
{
    protected $description;

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description ?: parent::getDescription();
    }
}
