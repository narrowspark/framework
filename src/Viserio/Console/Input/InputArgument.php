<?php
namespace Viserio\Console\Input;

use Symfony\Component\Console\Input\InputArgument as SymfonyInputArgument;

/**
 * InputArgument.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
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
