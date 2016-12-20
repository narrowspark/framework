<?php
declare(strict_types=1);
namespace Viserio\Http\Uri\Filter;

use Viserio\Http\Uri\Traits\TranscoderTrait;

class Query
{
    use TranscoderTrait;

    /**
     * Filter a query string to ensure it is propertly encoded.
     *
     * Ensures that the values in the query string are properly urlencoded.
     *
     * @param array $query
     *
     * @return string
     */
    public function build(array $query): string
    {
        $arr = array_map(function ($value) {
            return ! is_array($value) ? [$value] : $value;
        }, $query);

        $pairs = [];

        $encoder = [$this, 'encodeQueryFragment'];

        foreach ($arr as $key => $values) {
            $pairs = array_merge($pairs, $this->buildPair($encoder, $values, $encoder($key)));
        }

        return implode('&', $pairs);
    }

    /**
     * Parse a query.
     *
     * @param string $query
     *
     * @return arry
     */
    public function parse(string $query): array
    {
        $res = [];

        if ($query === '') {
            return $res;
        }

        foreach (explode('&', $query) as $pair) {
            $res = $this->parsePair($res, [$this, 'decodeQueryFragment'], $pair);
        }

        return $res;
    }

    /**
     * Build a query key/pair association.
     *
     * @param callable $encoder a callable to encode the key/pair association
     * @param array    $value   The query string value
     * @param string   $key     The query string key
     *
     * @return array
     */
    protected function buildPair(callable $encoder, array $value, string $key): array
    {
        $reducer = function (array $carry, $data) use ($key, $encoder) {
            $pair = $key;

            if ($data !== null) {
                $pair .= '=' . call_user_func($encoder, $data);
            }

            $carry[] = $pair;

            return $carry;
        };

        return array_reduce($value, $reducer, []);
    }

    /**
     * Parse a query string pair.
     *
     * @param array    $res     The associative array to add the pair to
     * @param callable $decoder a Callable to decode the query string pair
     * @param string   $pair    The query string pair
     *
     * @return array
     */
    protected function parsePair(array $res, callable $decoder, string $pair): array
    {
        $param = explode('=', $pair, 2);
        $key   = $decoder(array_shift($param));
        $value = array_shift($param);

        if ($value !== null) {
            $value = $decoder($value);
        }

        if (! array_key_exists($key, $res)) {
            $res[$key] = $value;

            return $res;
        }

        if (! is_array($res[$key])) {
            $res[$key] = [$res[$key]];
        }

        $res[$key][] = $value;

        return $res;
    }
}
