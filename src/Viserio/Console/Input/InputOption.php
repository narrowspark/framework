<?php
namespace Viserio\Console\Input;

use Symfony\Component\Console\Input\InputOption as SymfonyInputOption;

/**
 * InputArgument.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class InputOption extends SymfonyInputOption
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
