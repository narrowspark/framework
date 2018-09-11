<?php
declare(strict_types=1);
namespace Viserio\Component\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as RespectValidator;
use Viserio\Component\Contract\Translation\Traits\TranslatorAwareTrait;
use Viserio\Component\Contract\Validation\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Validation\Validator as ValidatorContract;

class Validator implements ValidatorContract
{
    use TranslatorAwareTrait;

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
     * Create new validator instance.
     */
    public function __construct()
    {
        RespectValidator::with('Viserio\\Component\\Validation\\Rules');
    }

    /**
     * Add your own rule's namespace.
     *
     * @param string $namespace
     * @param bool   $overwrite
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function with(string $namespace, bool $overwrite = false): void
    {
        RespectValidator::with($namespace, $overwrite);
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
                $rule = $this->createRule($fieldRules);
            }

            $data = $preparedData[$fieldName] ?? $preparedData;

            try {
                if (\is_array($data)) {
                    foreach ($data as $value) {
                        $rule->setName(\ucfirst($fieldName))->assert($value);
                    }
                } else {
                    $rule->setName(\ucfirst($fieldName))->assert($data);
                }

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
        return \count($this->failedRules) === 0;
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
        return ! $this->passes();
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
            if (\is_array($value)) {
                return $this->parseData($value);
            }
            $newData[$key] = $value;
        }

        return $newData;
    }

    /**
     * Create a rule object.
     *
     * @param array|string $rules
     *
     * @return \Respect\Validation\Validator
     */
    protected function createRule($rules): RespectValidator
    {
        $notRules      = [];
        $optionalRules = [];

        if (\is_string($rules)) {
            // remove duplicate
            $rules = \array_unique(\explode('|', $rules));
        }

        foreach ($rules as $key => $rule) {
            if (\mb_strpos($rule, '!') !== false) {
                $notRules[] = $rule;

                unset($rules[$key]);
            } elseif (\mb_strpos($rule, '?') !== false) {
                $optionalRules[] = $rule;

                unset($rules[$key]);
            }
        }

        // reset keys
        $rules     = \array_values($rules);
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
        if (\count($notRules) !== 0 && \count($optionalRules) !== 0) {
            throw new InvalidArgumentException('Not (!) and optional (?) cant be used at the same time.');
        }

        if (\count($notRules) !== 0) {
            return $this->createNegativeOrOptionalValidator('!', $notRules);
        }

        if (\count($optionalRules) !== 0) {
            return $this->createNegativeOrOptionalValidator('?', $optionalRules);
        }

        [$method, $parameters] = $this->parseStringRule($rules[0]);

        unset($rules[0]);

        return RespectValidator::$method(...$parameters);
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
        [$method, $parameters] = $this->parseStringRule($rules[0]);

        unset($rules[0]);

        $method    = \str_replace($filter, '', $method);
        $validator = RespectValidator::$method(...$parameters);

        if ($filter === '!') {
            return RespectValidator::not($this->createChainableValidators($validator, $rules));
        }

        return RespectValidator::optional($this->createChainableValidators($validator, $rules));
    }

    /**
     * Chain validator to a chained validator object.
     *
     * @param \Respect\Validation\Validator $class
     * @param array                         $rules
     *
     * @return \Respect\Validation\Validator
     */
    protected function createChainableValidators(RespectValidator $class, array $rules): RespectValidator
    {
        // reset keys
        $rules = \array_values($rules);

        if (\count($rules) !== 0) {
            $chain = '';

            foreach ($rules as $rule) {
                if ($rules[0] === $rule) {
                    $chain .= $rule;
                } else {
                    $chain .= '.' . $rule;
                }
            }

            return \array_reduce(\explode('.', $chain), function (object $validator, string $method) {
                [$method, $parameters] = $this->parseStringRule($method);
                $method                = \str_replace(['!', '?'], '', $method);

                return $validator->{$method}(...$parameters);
            }, $class);
        }

        return $class;
    }

    /**
     * Parse a string based rule.
     *
     * @param string $rules
     *
     * @return array
     */
    protected function parseStringRule(string $rules): array
    {
        $parameters = [];

        // The format for specifying validation rules and parameters follows an
        // easy {rule}:{parameters} formatting convention. For instance the
        // rule "Min:3" states that the value may only be three letters.
        if (\mb_strpos($rules, ':') !== false) {
            [$rules, $parameter] = \explode(':', $rules, 2);

            $parameters = $this->parseParameters($rules, $parameter);
        }

        return [\trim($rules), $parameters];
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
        if (\mb_strtolower($rule) === 'regex') {
            return [$parameter];
        }

        return \str_getcsv($parameter);
    }
}
