<?php
namespace Viserio\Console\Input;

use Symfony\Component\Console\Input\InputOption as SymfonyInputOption;

class InputOption extends SymfonyInputOption
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
