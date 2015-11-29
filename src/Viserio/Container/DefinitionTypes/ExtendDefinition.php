<?php
namespace Viserio\Container\DefinitionTypes;

use Viserio\Container\interfaces\ExtendDefinitionInterface;

class ExtendDefinition extends ObjectDefinition implements ExtendDefinitionInterface
{
    /**
     * @var string
     */
    protected $extended;

    /**
     * ExtendDefinition constructor.
     *
     * @param string $extended
     */
    public function __construct($extended)
    {
        $this->extended = $extended;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtended()
    {
        return $this->extended;
    }
}
