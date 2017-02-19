<?php
namespace Viserio\Component\Validation;

use Closure;
use Narrowspark\Arr\Arr;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;

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
     * @param string $name
     * @param mixed  $callback
     */
    public function register(string $name, $callback)
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
        list($data, $rules) = $this->runGlobalSanitizers($rules, $data);

        $availableRules = Arr::only($rules, array_keys($data));

        foreach ($availableRules as $field => $ruleset) {
            $data[$field] = $this->sanitizeField($data, $field, $ruleset);
        }

        return $data;
    }

    /**
     * Execute a sanitizer using the appropriate method.
     *
     * @param mixed $sanitizer
     * @param mixed $parameters
     *
     * @return mixed
     */
    public function executeSanitizer($sanitizer, $parameters)
    {
        if (is_callable($sanitizer)) {
            return $sanitizer(...$parameters);
        }

        if ($sanitizer instanceof Closure) {
            return $sanitizer(extract($parameters));
        }

        if ($this->container !== null) {
            // Transform a container resolution to a callback.
            $sanitizer = $this->resolveCallback($sanitizer);

            if (is_callable($sanitizer)) {
                return $sanitizer(...$parameters);
            }
        }

        // If the sanitizer can't be called, return the passed value.
        return $parameters[0];
    }

    /**
     * Apply global sanitizer rules.
     *
     * @param array $rules
     * @param array $data
     *
     * @return array
     */
    protected function runGlobalSanitizers(array $rules, array $data): array
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
     * @param array  $data
     * @param string $field
     * @param mixed  $ruleset
     *
     * @return string
     */
    protected function sanitizeField(array $data, string $field, $ruleset): string
    {
        // If we have a piped ruleset, explode it.
        if (is_string($ruleset)) {
            $ruleset = explode('|', $ruleset);
        }

        // Get value from data array.
        $value = $data[$field];

        foreach ((array) $ruleset as $rule) {
            $parametersSet = [];

            if (strpos($rule, ':') !== false) {
                list($rule, $parameters) = explode(':', $rule);

                $parametersSet = explode(',', $parameters);
            }

            array_unshift($parametersSet, $value);

            // Retrieve a sanitizer by key.
            if (isset($this->sanitizers[$rule])) {
                $sanitizers = $this->sanitizers[$rule];
            } else {
                $sanitizers = $rule;
            }

            // Execute the sanitizer to mutate the value.
            $value = $this->executeSanitizer($sanitizers, $parametersSet);
        }

        return $value;
    }

    /**
     * Resolve a callback from a class and method pair.
     *
     * @param string $callback
     *
     * @return array
     */
    protected function resolveCallback(string $callback): array
    {
        $segments = explode('@', $callback);
        $method = count($segments) == 2 ? $segments[1] : 'sanitize';

        // Return the constructed callback.
        return [$this->container->get($segments[0]), $method];
    }
}
