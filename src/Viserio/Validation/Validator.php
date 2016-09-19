<?php
declare(strict_types=1);
namespace Viserio\Validation;

use Respect\Validation\Validator as RespectValidator;
use Viserio\Contracts\Translation\Traits\TranslationAwareTrait;
use Viserio\Contracts\Validation\Validator as ValidatorContract;

class Validator implements ValidatorContract
{
    use TranslationAwareTrait;

    /**
     * Define a set of rules that apply to each element in an array attribute.
     *
     * @param string       $attribute
     * @param string|array $rules
     *
     * @throws \InvalidArgumentException
     */
    public function each(string $attribute, $rules)
    {
    }

    /**
     * Determine if the data passes the validation rules.
     *
     * @return bool
     */
    public function passes(): bool
    {
    }

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails(): bool
    {
    }

    /**
     * Create a chained rule object.
     *
     * @param array $rules
     *
     * @return \Respect\Validation\Validator
     */
    protected function createRule(array $rules): RespectValidator
    {
        $chain = '';
        $validator = RespectValidator::{strtolower($rules[0])}();

        unset($rules[0]);

        if (count($rules) !== 0) {
            foreach ($rules as $rule) {
                $chain .= strtolower('.' . $rule);
            }

            return array_reduce(explode('.', $chain), function ($obj, $method) {
                return $obj->$method();
            }, $validator);
        }

        return $validator;
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
        // rule "Max:3" states that the value may only be three letters.
        if (strpos($rules, ':') !== false) {
            list($rules, $parameter) = explode(':', $rules, 2);
            $parameters = $this->parseParameters($rules, $parameter);
        }

        return [Str::studly(trim($rules)), $parameters];
    }

    /**
     * Explode the rules into an array of rules.
     *
     * @param string|array $rules
     *
     * @return array
     */
    protected function explodeRules($rules)
    {
        foreach ($rules as $key => $rule) {
            if (Str::contains($key, '*')) {
                $this->each($key, [$rule]);

                unset($rules[$key]);
            } else {
                $rules[$key] = (is_string($rule)) ? explode('|', $rule) : $rule;
            }
        }

        return $rules;
    }
}
