<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Input;

use Symfony\Component\Console\Input\InputOption as SymfonyInputOption;

class InputOption extends SymfonyInputOption
{
    /**
     * Input option description.
     *
     * @var string
     */
    protected $description;

    /**
     * Set the input option description.
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
