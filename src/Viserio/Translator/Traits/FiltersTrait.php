<?php
namespace Viserio\Translator\Traits;

trait FiltersTrait
{
    /**
     * All registred filters.
     *
     * @var array
     */
    private $filters = [];

    /**
     * @param string|array $translation
     *
     * @return array
     */
    public function applyFilters($translation)
    {
        if (is_array($translation)) {
            $manager = $this;

            return array_map(function ($t) use ($manager) {
                return $manager->applyFilters($t);
            }, $translation);
        }

        foreach ($this->filters as $filter) {
            $translation = $filter($translation);
        }

        return $translation;
    }

    /**
     * Add filter.
     *
     * @param callable $filter
     */
    public function addFilter(callable $filter)
    {
        $this->filters[] = $filter;
    }
}
