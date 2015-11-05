<?php
namespace Brainwave\Translator\Traits;

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
 * ReplacementTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
trait ReplacementTrait
{
    /**
     * All added replacements.
     *
     * @var array
     */
    protected $replacements = [];

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
            throw new \InvalidArgumentException(sprintf('Replacement [%s] was not found.', $search));
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
                $message = preg_replace('~%'.$countame.'%~', $value, $message);
            }
        }

        return $message;
    }
}
