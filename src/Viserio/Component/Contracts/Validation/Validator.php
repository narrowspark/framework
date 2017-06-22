<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Validation;

interface Validator
{
    /**
     * Add your own rule's namespace.
     *
     * @param string $namespace
     *
     * @return void
     */
    public function with(string $namespace): void;

    /**
     * Run the validator rules against given data.
     *
     * @param array $data
     * @param array $rules
     *
     * @return $this
     */
    public function validate(array $data, array $rules): Validator;

    /**
     * Determine if the data passes the validation rules.
     *
     * @return bool
     */
    public function passes(): bool;

    /**
     * Returns the data which was valid.
     *
     * @return array
     */
    public function valid(): array;

    /**
     * Determine if the data fails the validation rules.
     *
     * @return bool
     */
    public function fails(): bool;

    /**
     * Returns the data which was invalid.
     *
     * @return array
     */
    public function invalid(): array;
}
