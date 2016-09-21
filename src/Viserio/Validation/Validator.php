<?php
declare(strict_types=1);
namespace Viserio\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as RespectValidator;
use RuntimeException;
use Viserio\Contracts\Translation\Traits\TranslationAwareTrait;
use Viserio\Contracts\Validation\Validator as ValidatorContract;

class Validator implements ValidatorContract
{
    use TranslationAwareTrait;

    /**
     * The failed validation rules.
     *
     * @var array
     */
    protected $failedRules = [];

    /**
     * The valid validation rules.
     *
     * @var array
     */
    protected $validRules = [];

    /**
     * Creat new validator instance.
     */
    public function __construct()
    {
        RespectValidator::with('Viserio\\Validation\\Rules');
    }

    /**
     * Add your own rule's namespace.
     *
     * @param string $namespace
     */
    public function with(string $namespace)
    {
        RespectValidator::with($namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $data, array $rules): ValidatorContract
    {
        $preparedData = $this->parseData($data);

        foreach ($rules as $fieldName => $fieldRules) {
            if ($fieldRules instanceof RespectValidator) {
                $rule = $fieldRules;
            } else {
                if (is_string($fieldRules)) {
                    // remove duplicate
                    $fieldRules = array_unique(explode('|', $fieldRules));
                }

                $rule = $this->createRule($fieldRules);
            }

            $data = $preparedData[$fieldName];

            try {
                $rule->setName(ucfirst($fieldName))->assert($data);

                $this->validRules[$fieldName] = true;
            } catch (NestedValidationException $exception) {
                $this->failedRules[$fieldName] = $exception->getMessages();
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function passes(): bool
    {
        return empty($this->failedRules);
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): array
    {
        return $this->validRules;
    }

    /**
     * {@inheritdoc}
     */
    public function fails(): bool
    {
        return ! empty($this->failedRules);
    }

    /**
     * {@inheritdoc}
     */
    public function invalid(): array
    {
        return $this->failedRules;
    }

    /**
     * Parse the data array.
     *
     * @param array $data
     *
     * @return array
     */
    protected function parseData(array $data): array
    {
        $newData = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->parseData($value);
            } else {
                $newData[$key] = $value;
            }
        }

        return $newData;
    }

    /**
     * Create a rule object.
     *
     * @param array $rules
     *
     * @return \Respect\Validation\Validator
     */
    protected function createRule(array $rules): RespectValidator
    {
        $notRules = [];
        $optionalRules = [];

        foreach ($rules as $key => $rule) {
            if (strpos($rule, '!') !== false) {
                $notRules[] = $rule;

                unset($rules[$key]);
            } elseif (strpos($rule, '?') !== false) {
                $optionalRules[] = $rule;

                unset($rules[$key]);
            }
        }

        // reset keys
        $rules = array_values($rules);

        $validator = $this->createValidator($rules, $notRules, $optionalRules);

        return $this->createChainableValidators($validator, $rules);
    }

    /**
     * Create a validator instance.
     *
     * @param array $rules
     * @param array $notRules
     * @param array $optionalRules
     *
     * @throws \RuntimeException
     *
     * @return \Respect\Validation\Validator
     */
    protected function createValidator(array &$rules, array $notRules, array $optionalRules): RespectValidator
    {
        if (count($notRules) !== 0 && count($optionalRules) !== 0) {
            throw new RuntimeException('Not (!) and optional (?) cant be used at the same time.');
        } elseif (count($notRules) !== 0) {
            return $this->createNegativeOrOptionalValidator('!', $notRules);
        } elseif (count($optionalRules) !== 0) {
            return $this->createNegativeOrOptionalValidator('?', $optionalRules);
        }

        list($method, $parameters) = $this->parseStringRule($rules[0]);

        unset($rules[0]);

        return call_user_func_array([RespectValidator::class, $method], $parameters);
    }

    /**
     * Create a negative or optional validator instance.
     *
     * @param string $filter
     * @param array  $rules
     *
     * @return \Respect\Validation\Validator
     */
    protected function createNegativeOrOptionalValidator(string $filter, array $rules): RespectValidator
    {
        list($method, $parameters) = $this->parseStringRule($rules[0]);

        unset($rules[0]);

        $validator = call_user_func_array(
            [RespectValidator::class, str_replace($filter, '', $method)],
            $parameters
        );

        if ($filter === '!') {
            return RespectValidator::not($this->createChainableValidators($validator, $rules));
        }

        return RespectValidator::optional($this->createChainableValidators($validator, $rules));
    }

    /**
     * Chain validators to a chanined validator object.
     *
     * @param string|\Respect\Validation\Validator $class
     * @param array                                $rules
     *
     * @return \Respect\Validation\Validator
     */
    protected function createChainableValidators($class, array $rules): RespectValidator
    {
        // reset keys
        $rules = array_values($rules);

        if (count($rules) !== 0) {
            $chain = '';

            foreach ($rules as $rule) {
                if ($rules[0] === $rule) {
                    $chain .= $rule;
                } else {
                    $chain .= '.' . $rule;
                }
            }

            return array_reduce(explode('.', $chain), function ($validator, $method) {
                list($method, $parameters) = $this->parseStringRule($method);

                $method = str_replace(['!', '?'], '', $method);

                return call_user_func_array([$validator, $method], $parameters);
            }, $class);
        }

        return $class;
    }

    /**
     * Parse a string based rule.
     *
     * @param string $rules
     *
     * @return array<string|array>
     */
    protected function parseStringRule(string $rules): array
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Min:3" states that the value may only be three letters.
        if (strpos($rules, ':') !== false) {
            list($rules, $parameter) = explode(':', $rules, 2);

            $parameters = $this->parseParameters($rules, $parameter);
        }

        return [trim($rules), $parameters];
    }

    /**
     * Parse a parameter list.
     *
     * @param string $rule
     * @param string $parameter
     *
     * @return array
     */
    protected function parseParameters(string $rule, string $parameter): array
    {
        if (strtolower($rule) == 'regex') {
            return [$parameter];
        }

        return str_getcsv($parameter);
    }
}
