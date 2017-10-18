<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Filter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Contract\Exception\Filter as FilterContract;

class ContentTypeFilter implements FilterContract
{
    /**
     * @var array
     */
    private $acceptableContentTypes;

    /**
     * {@inheritdoc}
     */
    public function filter(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        foreach ($displayers as $index => $displayer) {
            if (! $this->accepts($displayer->getContentType(), $request)) {
                unset($displayers[$index]);
            }
        }

        return \array_values($displayers);
    }

    /**
     * Determines whether the current requests accepts a given content type.
     *
     * @param string|array                             $contentTypes
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    private function accepts($contentTypes, ServerRequestInterface $request): bool
    {
        $accepts = $this->getAcceptableContentTypes($request->getHeaderLine('content-type'));

        if (\count($accepts) === 0) {
            return true;
        }

        foreach ($accepts as $accept) {
            if ($accept === '*/*' || $accept === '*') {
                return true;
            }

            foreach ((array) $contentTypes as $type) {
                if ($this->matchesType($accept, $type) || $accept === \strtok($type, '/') . '/*') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the given content types match.
     *
     * @param string $actual
     * @param string $type
     *
     * @return bool
     */
    private function matchesType(string $actual, string $type): bool
    {
        if ($actual === $type) {
            return true;
        }

        $split = \explode('/', $actual);

        return isset($split[1]) && \preg_match('#' . \preg_quote($split[0], '#') . '/.+\+' . \preg_quote($split[1], '#') . '#', $type);
    }

    /**
     * Gets a list of content types acceptable by the client browser.
     *
     * @param string $headerValue
     *
     * @return array List of content types in preferable order
     */
    private function getAcceptableContentTypes(string $headerValue): array
    {
        if (null !== $this->acceptableContentTypes) {
            return $this->acceptableContentTypes;
        }

        return $this->acceptableContentTypes = \array_map(
            function ($itemValue) {
                $bits = \preg_split('/\s*(?:;*("[^"]+");*|;*(\'[^\']+\');*|;+)\s*/', $itemValue, 0, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE);
                $value = \array_shift($bits);

                if (($start = \mb_substr($value, 0, 1)) === \mb_substr($value, -1) &&
                    ('"' === $start || '\'' === $start)
                ) {
                    return \mb_substr($value, 1, -1);
                }

                return $value;
            },
            \preg_split(
                '/\s*(?:,*("[^"]+"),*|,*(\'[^\']+\'),*|,+)\s*/',
                $headerValue,
                0,
                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
            )
        );
    }
}
