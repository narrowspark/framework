<?php
declare(strict_types=1);
namespace Viserio\Validation;

use Respect\Validation\Validator as RespectValidator;
use Viserio\Contracts\Translation\Traits\TranslationAwareTrait;
use Viserio\Contracts\Validation\Validator as ValidatorContract;
use Respect\Validation\Exceptions\NestedValidationException;

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
     * [__construct description]
     */
    public function __construct()
    {
        RespectValidator::with('Viserio\\Validation\\Rules');
    }

    /**
     * [with description]
     *
     * @param  string $namespace
     */
    public function with(string $namespace)
    {
        RespectValidator::with($namespace);
    }

    /**
     * [validate description]
     *
     * @param array  $data
     * @param array $rules
     *
     * @return $this
     */
    public function validate(array $data, array $rules): ValidatorContract
    {
        $preparedData = $this->parseData($data);

        foreach ($rules as $fieldName => $fieldRules) {
            if ($fieldRules instanceof RespectValidator) {
                $rule = $fieldRules;
            } else {
                //Explode the rules into an array of rules.
                $fieldRules = (is_string($fieldRules)) ? explode('|', $fieldRules) : $fieldRules;

                $data = $preparedData[$fieldName];

                $rule = $this->createRule($fieldRules);
            }

            try {
                $rule->setName(ucfirst($fieldName))->assert($data);

                $this->validRules[$fieldName] = true;
            } catch (NestedValidationException $exception){
                $this->failedRules[$fieldName] = $exception->getMessages();
            }
        }

        return $this;
    }

    /**
     * Determine if the data passes the validation rules.
     *
     * @return bool
     */
    public function passes(): bool
    {
        return empty($this->failedRules);
    }

    /**
     * Returns the data which was valid.
     *
     * @return array
     */
    public function valid(): array
    {
        return $this->validRules;
    }

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails(): bool
    {
        return !empty($this->failedRules);
    }

    /**
     * Returns the data which was invalid.
     *
     * @return array
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

        list($method, $parameters) = $this->parseStringRule($rules[0]);

        $validator = $this->createValidator(RespectValidator::class, $method, $parameters);

        unset($rules[0]);

        $chain = '';

        if (count($rules) !== 0) {
            foreach ($rules as $rule) {
                if ($rules[1] === $rule) {
                    $chain .= $rule;
                } else {
                    $chain .= '.' . $rule;
                }
            }

            return array_reduce(explode('.', $chain), function ($class, $method) {
                list($method, $parameters) = $this->parseStringRule($method);

                return call_user_func_array([$class, $method], $parameters);
            }, $validator);
        }

        return $validator;
    }

    /**
     * Create a validator instance.
     *
     * @param \Respect\Validation\Validator|string $class
     * @param string                               $method
     * @param array                                $parameters
     *
     * @return \Respect\Validation\Validator
     */
    public function createValidator($class, string $method, array $parameters): RespectValidator
    {
        if ($negativeValidator = $this->createNegativeValidator($class, $method, $parameters)) {
            return $negativeValidator;
        } elseif ($optionalValidator = $this->createOptionalValidator($class, $method, $parameters)) {
            return $optionalValidator;
        }

        return call_user_func_array([$class, $method], $parameters);
    }

    /**
     * Create a negative validator instance.
     *
     * @param \Respect\Validation\Validator|string $class
     * @param string                               $method
     * @param array                                $parameters
     *
     * @return \Respect\Validation\Validator|null
     */
    protected function createNegativeValidator($class, string $method, array $parameters)
    {
        $isNegative = strpos($method, '!');

        if ($isNegative !== false) {
            $method = str_replace('!', '', $method);

            $validator = call_user_func_array([$class, $method], $parameters);

            return RespectValidator::not($validator);
        }

        return null;
    }

    /**
     * Create a optional validator instance.
     *
     * @param \Respect\Validation\Validator|string $class
     * @param string                               $method
     * @param array                                $parameters
     *
     * @return \Respect\Validation\Validator|null
     */
    protected function createOptionalValidator($class, string $method, array $parameters)
    {
        $isOptional = strpos($method, '?');

        if ($isOptional !== false) {
            $method = str_replace('?', '', $method);

            $validator = call_user_func_array([$class, $method], $parameters);

            return RespectValidator::optional($validator);
        }

        return null;
    }

    /**
     * Parse a string based rule.
     *
     * @param string $rules
     *
     * @return array
     */
    protected function parseStringRule($rules)
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
