<?php
namespace Viserio\Translation;

use InvalidArgumentException;
use RuntimeException;
use Viserio\Contracts\Translation\Translator as TranslatorContract;
use Viserio\Translation\Traits\ValidateLocaleTrait;

class Translator implements TranslatorContract
{
    use ValidateLocaleTrait;

    /**
     * All added replacements.
     *
     * @var array
     */
    private $replacements = [];

    /**
     * All registred filters.
     *
     * @var array
     */
    private $filters = [];

    /**
     * All registred helpers.
     *
     * @var array
     */
    private $helpers = [];

    /**
     * Default language to translate into. (e.g. 'en').
     *
     * @var string
     */
    private $locale;

    /**
     * {@inheritdoc}
     */
    public function setLocale(string $locale): TranslatorContract
    {
        $this->assertValidLocale($locale);
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function trans(
        string $id,
        array $parameters = [],
        string $domain = null,
        string $locale = null
    ): string {
        $locale = (null !== $locale) ? $this->assertValidLocale($locale) : $this->getLocale();

        if (is_numeric($context)) {
            // Get plural form
            $string = $this->plural($string, $context, $locale);
        } else {
            // Get custom form
            $string = $this->form($string, $context, $locale);
        }

        return empty($values) ? $string : strtr($string, $values);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice(
        string $id,
        int $number,
        array $parameters = [],
        string $domain = null,
        string $locale = null
    ): string {
        $this->assertValidLocale($locale);

        // TODO finish
    }

    /**
     * {@inheritdoc}
     */
    public function addHelper(string $name, callable $helper): TranslatorContract
    {
        $this->helpers[$name] = $helper;

        return $this;
    }

    /**
     * Apply helpers.
     *
     * @param string[] $translation
     * @param array    $helpers
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function applyHelpers(array $translation, array $helpers): array
    {
        if (is_array($translation)) {
            $translator = $this;

            return array_map(function ($trans) use ($translator, $helpers) {
                return $translator->applyHelpers($trans, $helpers);
            }, $translation);
        }

        foreach ($helpers as $helper) {
            if (!isset($this->helpers[$helper['name']])) {
                throw new RuntimeException('Helper ' . $helper['name'] . ' is not registered.');
            }

            array_unshift($helper['arguments'], $translation);

            $translation = call_user_func_array($this->helpers[$helper['name']], $helper['arguments']);
        }

        return $translation;
    }

    /**
     * Returns translation of a string. If no translation exists, the original string will be
     * returned. No parameters are replaced.
     *
     * @param string      $string
     * @param int         $count
     * @param string|null $locale
     *
     * @return string
     */
    public function plural(string $string, int $count = 0, $locale = null): string
    {
        $this->assertValidLocale($locale);

        // Get the translation form key
        $form = $this->getPluralization()->get($count, $locale);

        // Return the translation for that form
        return $this->form($string, $form, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(string $name, callable $filter): TranslatorContract
    {
        $this->filters[$name] = $filter;

        return $this;
    }

    /**
     * @param string|array $translation
     * @param array        $filters
     *
     * @return array
     */
    public function applyFilters($translation, array $filters): array
    {
        if (is_array($translation)) {
            $translator = $this;

            return array_map(function ($translation) use ($translator) {
                return $translator->applyFilters($translation);
            }, $translation);
        }

        foreach ($this->filters as $filter) {
            $translation = $filter($translation);
        }

        return $translation;
    }

    /**
     * Add replacement.
     *
     * @param string $search
     * @param string $replacement
     *
     * @return self
     */
    public function addReplacement($search, $replacement)
    {
        $this->replacements[$search] = $replacement;

        return $this;
    }

    /**
     * Remove replacements.
     *
     * @param string $search
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function removeReplacement($search)
    {
        if (!isset($this->replacements[$search])) {
            throw new InvalidArgumentException(sprintf('Replacement [%s] was not found.', $search));
        }

        unset($this->replacements[$search]);

        return $this;
    }

    /**
     * @return array
     */
    public function getReplacements()
    {
        return $this->replacements;
    }

    /**
     * Description.
     *
     * @param string $message
     * @param array  $args
     *
     * @return string
     */
    protected function applyReplacements($message, array $args = [])
    {
        $replacements = $this->replacements;

        foreach ($args as $countame => $value) {
            $replacements[$countame] = $value;
        }

        foreach ($replacements as $countame => $value) {
            if ($value !== false) {
                $message = preg_replace('~%' . $countame . '%~', $value, $message);
            }
        }

        return $message;
    }
}
