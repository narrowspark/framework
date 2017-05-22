<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Input;

use Symfony\Component\Console\Input\InputArgument as SymfonyInputArgument;

class InputArgument extends SymfonyInputArgument
{
    /**
     * Input argument description.
     *
     * @var string
     */
    protected $description;

    /**
     * Set the input argument description.
     *
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description ?: parent::getDescription();
    }
}
