<?php
namespace Viserio\Console\Input;

use Symfony\Component\Console\Input\InputArgument as SymfonyInputArgument;

class InputArgument extends SymfonyInputArgument
{
    protected $description;

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description ?: parent::getDescription();
    }
}
