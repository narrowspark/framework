<?php
namespace Viserio\Translator\Traits;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

/**
 * FiltersTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
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
