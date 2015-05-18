<?php

namespace Brainwave\Log\Traits;

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
 * @version     0.9.8-dev
 */

/**
 * ProcessorTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
trait ProcessorTrait
{
    /**
     * Parse Processor.
     *
     * @param object            $handler
     * @param array|object|null $processors
     *
     * @return object
     */
    protected function parseProcessor($handler, $processors = null)
    {
        if (is_array($processors)) {
            foreach ($processors as $processor => $settings) {
                $handler->pushProcessor(new $processor($settings));
            }
        } elseif (null !== $processors) {
            $handler->pushProcessor($processors);
        }

        return $handler;
    }
}
