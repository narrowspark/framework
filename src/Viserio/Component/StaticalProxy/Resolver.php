<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy;

use Viserio\Component\StaticalProxy\Traits\ExistTrait;

final class Resolver
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
     * @var callable|string
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
     * @param callable|string $translation
     */
    public function __construct(string $pattern, $translation)
    {
        $regex             = \preg_quote($pattern, '#');
        $this->regex       = '#^' . \str_replace('\\*', '(.*)', $regex) . '$#uD';
        $this->pattern     = $pattern;
        $this->translation = $translation;
    }

    /**
     * Resolves an alias.
     *
     * @param string $alias
     *
     * @return null|string
     */
    public function resolve(string $alias): ?string
    {
        // Check wether the alias matches the pattern
        if (\preg_match($this->regex, $alias, $matches) !== 1) {
            return null;
        }

        // Get the translation
        $translation = $this->translation;

        if (\mb_strpos($translation, '$') === false) {
            $class = $translation;
        } else {
            // Make sure namespace seperators are escaped
            $translation = \str_replace('\\', '\\\\', $translation);
            // Resolve the replacement
            $class = \preg_replace($this->regex, $translation, $alias);
        }

        // Check wether the class exists
        if ($class && $this->exists($class, true)) {
            return $class;
        }

        return null;
    }

    /**
     * Checks whether the resolver matches a given pattern and optional translation.
     *
     * @param string      $pattern
     * @param null|string $translation
     *
     * @return bool
     */
    public function matches(string $pattern, string $translation = null): bool
    {
        return $this->pattern === $pattern && (! $translation || $translation === $this->translation);
    }
}
