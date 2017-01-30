<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig;

use Twig_Environment;
use Twig_LoaderInterface;
use Viserio\Component\View\Traits\NormalizeNameTrait;

class TwigEnvironment extends Twig_Environment
{
    use NormalizeNameTrait;

    /**
     * Twig options.
     *
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function __construct(Twig_LoaderInterface $loader, array $options)
    {
        $this->options = $options;

        parent::__construct($loader, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function loadTemplate($name, $index = null)
    {
        $template = parent::loadTemplate($name, $index);
        $template->setName($this->normalizedName($name));

        return $template;
    }

    /**
     * Normalize a view name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizedName(string $name): string
    {
        if (isset($this->options['file_extensions']) && is_array($this->options['file_extensions'])) {
            foreach ($this->options['file_extensions'] as $extension) {
                $extension = '.' . $extension;
                $length    = mb_strlen($extension);

                if (mb_substr($name, -$length, $length) === $extension) {
                    $name = mb_substr($name, 0, -$length);
                }
            }
        }

        return $this->normalizeName($name);
    }
}
