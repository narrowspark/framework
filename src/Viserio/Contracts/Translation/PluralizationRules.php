<?php
namespace Viserio\Contracts\Translation;

interface PluralizationRules
{
    /**
     * Returns the plural position to use for the given locale and number.
     *
     * @param int    $count
     * @param string $language
     *
     * @return int
     */
    public function get(int $count, string $language): int;

     /**
     * Overrides the default plural rule for a given locale.
     *
     * @param string   $language
     * @param callable $rule
     *
     * @return $this
     */
    public function set(string $language, callable $rule): PluralizationRules;
}
