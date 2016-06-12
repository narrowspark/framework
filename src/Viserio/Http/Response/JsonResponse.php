<?php
namespace Viserio\Http\Response;

use InvalidArgumentException;
use Viserio\Http\{
    Response,
    Stream
};
use Viserio\Http\Response\Traits\InjectContentTypeTrait;

class JsonResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * Default flags for json_encode; value of:
     *
     * <code>
     * JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
     * </code>
     *
     * @const int
     */
    const DEFAULT_JSON_FLAGS = 79;

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
     * @param mixed $data            Data to convert to JSON.
     * @param int   $status          Integer status code for the response; 200 by default.
     * @param array $headers         Array of headers to use at initialization.
     * @param int   $encodingOptions JSON encoding options to use.
     *
     * @throws InvalidArgumentException if unable to encode the $data to JSON.
     */
    public function __construct(
        $data,
        $status = 200,
        array $headers = [],
        $encodingOptions = self::DEFAULT_JSON_FLAGS
    ) {
        $body = new Stream(fopen('php://temp', 'wb+'));
        $body->write($this->jsonEncode($data, $encodingOptions));
        $body->rewind();

        $headers = $this->injectContentType('application/json', $headers);

        parent::__construct($status, $headers, $body);
    }

    /**
     * Encode the provided data to JSON.
     *
     * @param mixed $data
     * @param int   $encodingOptions
     *
     * @throws InvalidArgumentException if unable to encode the $data to JSON.
     *
     * @return string
     */
    private function jsonEncode($data, $encodingOptions)
    {
        if (is_resource($data)) {
            throw new InvalidArgumentException('Cannot JSON encode resources');
        }

        // Clear json_last_error()
        json_encode(null);

        $json = json_encode($data, $encodingOptions);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException(sprintf(
                'Unable to encode data to JSON in %s: %s',
                __CLASS__,
                json_last_error_msg()
            ));
        }

        return $json;
    }
}
