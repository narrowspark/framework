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
 * HelperTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
trait HelpersTrait
{
    /**
     * All registred helpers.
     *
     * @var array
     */
    private $helpers = [];

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
     * @throws \Exception
     *
     * @return mixed
     */
    public function applyHelpers($translation, array $helpers)
    {
        if (is_array($translation)) {
            $manager = $this;

            return array_map(function ($trans) use ($manager, $helpers) {
                return $manager->applyHelpers($trans, $helpers);
            }, $translation);
        }

        foreach ($helpers as $helper) {
            if (!isset($this->helpers[$helper['name']])) {
                throw new \Exception('Helper '.$helper['name'].' is not registered.');
            }

            array_unshift($helper['arguments'], $translation);

            $translation = call_user_func_array($this->helpers[$helper['name']], $helper['arguments']);
        }

        return $translation;
    }
}
