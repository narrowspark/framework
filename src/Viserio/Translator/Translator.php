<?php
namespace Viserio\Translator;

use InvalidArgumentException;
use RuntimeException;
use Viserio\Contracts\Translator\Translator as TranslatorContract;
use Viserio\Translator\Traits\ValidateLocaleTrait;

class Translator
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
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
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
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $this->assertValidLocale($locale);
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
    public function plural($string, $count = 0, $locale = null)
    {
        $this->assertValidLocale($locale);

        // Get the translation form key
        $form = $this->getPluralization()->get($count, $locale);

        // Return the translation for that form
        return $this->form($string, $form, $locale);
    }

    /**
     * Add helper.
     *
     * @param string   $name
     * @param callable $helper
     */
    public function addHelper($name, callable $helper)
    {
        $this->helpers[$name] = $helper;
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
    public function applyHelpers($translation, array $helpers)
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
     * Add filter.
     *
     * @param string   $name
     * @param callable $filter
     */
    public function addFilter($name, callable $filter)
    {
        $this->filters[$name] = $filter;
    }

    /**
     * @param string|array $translation
     * @param array        $filters
     *
     * @return array
     */
    public function applyFilters($translation, array $filters)
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
