<?php
namespace Viserio\Log\Traits;

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
