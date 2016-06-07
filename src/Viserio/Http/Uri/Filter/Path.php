<?php
namespace Viserio\Http\Uri\Filter;

use Viserio\Http\Uri\Traits\TranscoderTrait;

class Path
{
    use TranscoderTrait;

    /**
     * Dot Segment pattern
     *
     * @var array
     */
    protected static $dotSegments = ['.' => 1, '..' => 1];

    /**
     * Filter Path.
     *
     * @param string $path
     *
     * @return string
     */
    public function filter(string $path): string
    {
        $input = explode('/', $path);
        $newPath   = implode('/', array_reduce($input, [$this, 'filterDotSegments'], []));

        if (isset(static::$dotSegments[end($input)])) {
            $newPath .= '/';
        }

        $newPath = $this->withoutEmptySegments($newPath);
        $newPath = $this->upper($newPath);
        $newPath = $this->validate($newPath);

        return $this->encodePath(implode('/', $newPath));
    }

    /**
     * validate the submitted data
     *
     * @param string $data
     *
     * @return array
     */
    protected function validate($data)
    {
        $filterSegment = function ($segment) {
            return isset($segment);
        };

        $data = $this->decodePath($data);

        return array_filter(explode('/', $data), $filterSegment);
    }


    /**
     * Returns an instance without duplicate delimiters
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the path component normalized by removing
     * multiple consecutive empty segment
     *
     * @return string
     */
    public function withoutEmptySegments(string $path): string
    {
        return preg_replace(',/+,', '/', $path);
    }

    /**
     * Filter Dot segment according to RFC3986
     *
     * @see http://tools.ietf.org/html/rfc3986#section-5.2.4
     *
     * @param array  $carry   Path segments
     * @param string $segment a path segment
     *
     * @return array
     */
    protected function filterDotSegments(array $carry, string $segment): array
    {
        if ($segment == '..') {
            array_pop($carry);

            return $carry;
        }

        if (!isset(static::$dotSegments[$segment])) {
            $carry[] = $segment;
        }

        return $carry;
    }

    /**
     * @param array $match
     *
     * @return string
     */
    protected function rawurlencodeMatchZero(array $match): string
    {
        return rawurlencode(trim($match[0]));
    }

    /**
     * Convert to Uppercase a string.
     *
     * @param string $str
     *
     * @return string
     */
    protected function upper(string $path): string
    {
        return preg_replace_callback('/%[A-Fa-f0-9]{2}/', function($match) {
            return strtoupper($match[0]);
        }, $path);
    }
}
