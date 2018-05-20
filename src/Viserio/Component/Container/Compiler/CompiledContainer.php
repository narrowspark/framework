<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Container\Exception\ContainerException;
use Viserio\Component\Contract\Container\Exception\CyclicDependencyException;
use Viserio\Component\Contract\Container\Types as TypesContract;

class CompiledContainer extends Container
{
    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (! \is_string($id)) {
            throw new ContainerException(\sprintf(
                'The id parameter must be of type string, [%s] given.',
                \is_object($id) ? \get_class($id) : \gettype($id)
            ));
        }

        $binding = $this->bindings[$id] ?? null;

        if ($binding !== null && $this->isComputed($binding)) {
            return $binding[TypesContract::VALUE];
        }

        $method = self::$methodMapping[$id] ?? null;

        // If it's a compiled entry, then there is a method in this class
        if ($method !== null) {
            if (\in_array($id, $this->buildStack, true)) {
                $this->buildStack[] = $id;

                throw new CyclicDependencyException($id, $this->buildStack);
            }

            $this->buildStack[] = $id;

            try {
                $value = $this->$method();
            } finally {
                \array_pop($this->buildStack);
            }

            return $value;
        }

        return parent::get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        if (! \is_string($id)) {
            throw new ContainerException(\sprintf(
                'The id parameter must be of type string, [%s] given.',
                \is_object($id) ? \get_class($id) : \gettype($id)
            ));
        }

        // The parent method is overridden to check in our array, it avoids resolving definitions
        if (isset(self::$methodMapping[$id])) {
            return true;
        }

        return parent::has($id);
    }
}
