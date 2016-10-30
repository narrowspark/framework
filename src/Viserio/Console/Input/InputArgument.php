<?php
declare(strict_types=1);
namespace Viserio\Console\Input;

use Symfony\Component\Console\Input\InputArgument as SymfonyInputArgument;

class InputArgument extends SymfonyInputArgument
{
    protected $description;

    /**
     * Set the input argument description.
     *
     * @param string $description
     *
     * @codeCoverageIgnore
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getDescription()
    {
        return $this->description ?: parent::getDescription();
    }
}
