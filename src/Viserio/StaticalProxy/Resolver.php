<?php
namespace Viserio\StaticalProxy;

use Viserio\StaticalProxy\Traits\ExistTrait;

class Resolver
{
    use ExistTrait;

    /**
     * @var string
     */
    protected $regex;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * @var string|callable
     */
    protected $translation;

    /**
     * @var bool
     */
    protected $active = false;

    /**
     * @param string          $pattern
     * @param string|callable $translation
     */
    public function __construct($pattern, $translation)
    {
        $regex             = preg_quote($pattern, '#');
        $this->regex       = '#^' . str_replace('\\*', '(.*)', $regex) . '$#uD';
        $this->pattern     = $pattern;
        $this->translation = $translation;
    }

    /**
     * Resolves an alias
     *
     * @param string $alias
     *
     * @return bool
     */
    public function resolve($alias)
    {
        // Check wether the alias matches the pattern
        if (!preg_match($this->regex, $alias, $matches)) {
            return false;
        }

        // Get the translation
        $translation = $this->translation;

        if (strpos($translation, '$') === false) {
            $class = $translation;
        } else {
            // Make sure namespace seperators are escaped
            $translation = str_replace('\\', '\\\\', $translation);
            // Resolve the replacement
            $class = preg_replace($this->regex, $translation, $alias);
        }

        // Check wether the class exists
        if ($class && $this->exists($class, true)) {
            return $class;
        }

        return false;
    }

    /**
     * Checks whether the resolver matches a given pattern and optional translation
     *
     * @param string        $pattern
     * @param callable|null $translation
     *
     * @return bool
     */
    public function matches($pattern, $translation = null)
    {
        return $this->pattern === $pattern && (!$translation || $translation === $this->translation);
    }
}
