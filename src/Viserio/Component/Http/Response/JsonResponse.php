<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Response;

use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Http\Exception\RuntimeException;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Response\Traits\InjectContentTypeTrait;
use Viserio\Component\Http\Stream;

class JsonResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * Default flags for json_encode; value of.
     *
     * <code>
     * JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
     * </code>
     *
     * @const int
     */
    public const DEFAULT_JSON_FLAGS = 79;

    /**
     * Create a JSON response with the given data.
     *
     * Default JSON encoding is performed with the following options, which
     * produces RFC4627-compliant JSON, capable of embedding into HTML.
     *
     * - JSON_HEX_TAG
     * - JSON_HEX_APOS
     * - JSON_HEX_AMP
     * - JSON_HEX_QUOT
     * - JSON_UNESCAPED_SLASHES
     *
     * @param mixed       $data            data to convert to JSON
     * @param null|string $charset         content charset; default is utf-8
     * @param int         $status          integer status code for the response; 200 by default
     * @param array       $headers         array of headers to use at initialization
     * @param int         $encodingOptions jSON encoding options to use
     * @param string      $version         protocol version
     *
     * @throws \Narrowspark\HttpStatus\Exception\InvalidArgumentException
     * @throws \Viserio\Component\Contract\Http\Exception\RuntimeException
     * @throws \Viserio\Component\Contract\Http\Exception\UnexpectedValueException
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     */
    public function __construct(
        $data,
        ?string $charset = null,
        int $status = self::STATUS_OK,
        array $headers = [],
        int $encodingOptions = self::DEFAULT_JSON_FLAGS,
        string $version = '1.1'
    ) {
        $body = new Stream(\fopen('php://temp', 'wb+'));
        $body->write($this->jsonEncode($data, $encodingOptions));
        $body->rewind();

        $headers = $this->injectContentType('application/json; charset=' . ($charset ?? 'utf-8'), $headers);

        parent::__construct($status, $headers, $body, $version);
    }

    /**
     * Encode the provided data to JSON.
     *
     * @param mixed $data
     * @param int   $encodingOptions
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     * @throws \Viserio\Component\Contract\Http\Exception\RuntimeException         if unable to encode the $data to JSON
     *
     * @return string
     */
    private function jsonEncode($data, $encodingOptions): string
    {
        if (\is_resource($data)) {
            throw new InvalidArgumentException('Cannot JSON encode resources.');
        }

        // Clear json_last_error()
        \json_encode(null);

        $json = \json_encode($data, $encodingOptions);

        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new RuntimeException(\sprintf(
                'Unable to encode data to JSON in %s: %s',
                __CLASS__,
                \json_last_error_msg()
            ));
        }

        return $json;
    }
}
