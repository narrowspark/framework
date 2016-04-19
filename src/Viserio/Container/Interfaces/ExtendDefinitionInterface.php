<?php
namespace Viserio\Container\interfaces;

use Interop\Container\Definition\ObjectDefinitionInterface;

interface ExtendDefinitionInterface extends ObjectDefinitionInterface
{
    /**
     * @return string
     */
    public function getExtended();
}
