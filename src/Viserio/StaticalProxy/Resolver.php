<?php
declare(strict_types=1);
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
     * Create a new Resolver instance.
     *
     * @param string          $pattern
     * @param string|callable $translation
     */
    public function __construct(string $pattern, $translation)
    {
        $regex = preg_quote($pattern, '#');
        $this->regex = '#^' . str_replace('\\*', '(.*)', $regex) . '$#uD';
        $this->pattern = $pattern;
        $this->translation = $translation;
    }

    /**
     * Resolves an alias
     *
     * @param string $alias
     *
     * @return bool|object
     */
    public function resolve(string $alias)
    {
        // Check wether the alias matches the pattern
        if (! preg_match($this->regex, $alias, $matches)) {
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
     * @param string      $pattern
     * @param string|null $translation
     *
     * @return bool
     */
    public function matches(string $pattern, string $translation = null): bool
    {
        return $this->pattern === $pattern && (! $translation || $translation === $this->translation);
    }
}
