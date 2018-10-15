<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Formatter;

use IntlException;
use MessageFormatter;
use Viserio\Component\Contract\Translation\Exception\CannotFormatException;
use Viserio\Component\Contract\Translation\Exception\CannotInstantiateFormatterException;
use Viserio\Component\Contract\Translation\MessageFormatter as MessageFormatterContract;

class IntlMessageFormatter implements MessageFormatterContract
{
    /**
     * {@inheritdoc}
     */
    public function format(string $message, string $locale, array $parameters = []): string
    {
        if ($message === '') {
            // Empty strings are not accepted as message pattern by the \MessageFormatter.
            return $message;
        }

        try {
            $formatter = new MessageFormatter($locale, $message);
        } catch (IntlException $exception) {
            throw new CannotInstantiateFormatterException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        // @codeCoverageIgnoreStart
        if ($formatter === null) {
            throw new CannotInstantiateFormatterException(
                \intl_get_error_message(),
                \intl_get_error_code()
            );
        }
        // @codeCoverageIgnoreEnd
        $result = $formatter->format($parameters);

        if ($formatter->getErrorCode() !== \U_ZERO_ERROR) {
            throw new CannotFormatException(\sprintf('Unable to format message. Reason: %s (error #%s)', $formatter->getErrorMessage(), $formatter->getErrorCode()));
        }

        return $result;
    }
}
