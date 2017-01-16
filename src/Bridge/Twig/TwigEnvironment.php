<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig;

use Twig_Environment;
use Twig_LoaderInterface;
use Interop\Container\ContainerInterface;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\View\Finder as FinderContract;

class TwigEnvironment extends Twig_Environment
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct(Twig_LoaderInterface $loader, array $options = [], ContainerInterface $container)
    {
        parent::__construct($loader, $options);

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function loadTemplate($name, $index = null)
    {
        $template = parent::loadTemplate($name, $index);
        $template->setName($this->normalizeName($name));

        return $template;
    }

    /**
     * Normalize a view name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        if ($this->container->has('twig.extensions')) {
            foreach ((array) $this->container->get('twig.extensions') as $extension) {
                $extension = '.' . $extension;
                $length    = strlen($extension);

                if (substr($name, -$length, $length) === $extension) {
                    $name = substr($name, 0, -$length);
                }
            }
        }

        // Normalize namespace and delimiters
        $delimiter = FinderContract::HINT_PATH_DELIMITER;

        if (strpos($name, $delimiter) === false) {
            return str_replace('/', '.', $name);
        }

        list($namespace, $name) = explode($delimiter, $name);

        return $namespace.$delimiter.str_replace('/', '.', $name);
    }
}
