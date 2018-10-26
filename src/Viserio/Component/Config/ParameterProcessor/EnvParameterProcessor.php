<?php
declare(strict_types=1);
namespace Viserio\Component\Config\ParameterProcessor;

class EnvParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getReferenceKeyword(): string
    {
        return 'env';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        $parameterKey = $this->parseParameter($parameter);

        $value = \getenv($parameterKey);

        if ($value === false) {
            return $parameterKey;
        }

        if (\preg_match('/base64:|\'base64:|"base64:/', $value) === 1) {
            return \base64_decode(\mb_substr($value, 7), true);
        }

        if (\in_array(
            \mb_strtolower($value),
            [
                'false',
                '(false)',
                'true',
                '(true)',
                'yes',
                '(yes)',
                'no',
                '(no)',
                'on',
                '(on)',
                'off',
                '(off)',
            ],
            true
        )) {
            $value = \str_replace(['(', ')'], '', $value);

            return \filter_var(
                $value,
                \FILTER_VALIDATE_BOOLEAN,
                \FILTER_NULL_ON_FAILURE
            );
        }

        if ($value === 'null' || $value === '(null)') {
            return null;
        }

        if (\is_numeric($value)) {
            return $value + 0;
        }

        if ($value === 'empty' || $value === '(empty)') {
            return '';
        }

        if (\mb_strlen($value) > 1 &&
            \mb_substr($value, 0, \mb_strlen('"')) === '"' &&
            \mb_substr($value, -\mb_strlen('"')) === '"'
        ) {
            return \mb_substr($value, 1, -1);
        }

        return $value;
    }
}
