<?php
namespace Viserio\Component\Validation;

use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Validation\Exception\InvalidArgumentException;

class Sanitizer
{
    use ContainerAwareTrait;

    /**
     * Array of registered sanitizers.
     *
     * @var array
     */
    protected $sanitizers = [];

    /**
     * Register a new sanitization method.
     *
     * @param string                    $name
     * @param callable|string|\Closure  $callback
     *
     * @return void
     */
    public function register(string $name, $callback): void
    {
        $this->sanitizers[$name] = $callback;
    }

    /**
     * Sanitize a dataset using rules.
     *
     * @param array $rules
     * @param array $data
     *
     * @return array
     */
    public function sanitize(array $rules, array $data): array
    {
        [$data, $rules] = $this->runGlobalSanitizers($rules, $data);

        $availableRules = \array_intersect_key($rules, \array_flip(array_keys($data)));

        foreach ($availableRules as $field => $ruleset) {
            $data[$field] = $this->sanitizeField($data, $field, $ruleset);
        }

        return $data;
    }

    /**
     * Apply global sanitizer rules.
     *
     * @param array $rules
     * @param array $data
     *
     * @return array
     */
    private function runGlobalSanitizers(array $rules, array $data): array
    {
        // Bail out if no global rules were found.
        if (! isset($rules['*'])) {
            return [$data, $rules];
        }

        // Get the global rules and remove them from the main ruleset.
        $globalRules = $rules['*'];

        unset($rules['*']);

        // Execute the global sanitiers on each field.
        foreach ($data as $field => $value) {
            $data[$field] = $this->sanitizeField($data, $field, $globalRules);
        }

        return [$data, $rules];
    }

    /**
     * Execute sanitization over a specific field.
     *
     * @param array        $data
     * @param string       $field
     * @param string|array $ruleset
     *
     * @return string
     */
    private function sanitizeField(array $data, string $field, $ruleset): string
    {
        if (! \is_string($ruleset) && ! \is_array($ruleset)) {
            throw new InvalidArgumentException(\sprintf(
                'The ruleset parameter must be of type string or array, [%s] given.',
                \is_object($ruleset) ? \get_class($ruleset) : \gettype($ruleset)
            ));
        }

        // If we have a piped ruleset, explode it.
        if (\is_string($ruleset)) {
            $ruleset = \explode('|', $ruleset);
        }

        // Get value from data array.
        $value = $data[$field];

        foreach ($ruleset as $rule) {
            $parametersSet = [];

            if (\strpos($rule, ':') !== false) {
                [$rule, $parameters] = \explode(':', $rule);

                $parametersSet = \explode(',', $parameters);
            }

            \array_unshift($parametersSet, $value);

            $sanitizers = $rule;

            // Retrieve a sanitizer by key.
            if (isset($this->sanitizers[$rule])) {
                $sanitizers = $this->sanitizers[$rule];
            }

            // Execute the sanitizer to mutate the value.
            $value = $this->executeSanitizer($sanitizers, $parametersSet);
        }

        return $value;
    }

    /**
     * Execute a sanitizer using the appropriate method.
     *
     * @param callable|string|\Closure $sanitizer
     * @param array                    $parameters
     *
     * @return string
     */
    private function executeSanitizer($sanitizer, array $parameters): string
    {
        if (\is_callable($sanitizer)) {
            return $sanitizer(...$parameters);
        }

        if ($this->container !== null) {
            // Transform a container resolution to a callback.
            $sanitizer = $this->resolveCallback($sanitizer);

            return $sanitizer(...$parameters);
        }

        // If the sanitizer can't be called, return the passed value.
        return $parameters[0];
    }

    /**
     * Resolve a callback from a class and method pair.
     *
     * @param string $callback
     *
     * @return array<object, string>
     */
    private function resolveCallback(string $callback): array
    {
        $segments = explode('@', $callback);
        $method = \count($segments) === 2 ? $segments[1] : 'sanitize';

        // Return the constructed callback.
        return [$this->container->get($segments[0]), $method];
    }
}
