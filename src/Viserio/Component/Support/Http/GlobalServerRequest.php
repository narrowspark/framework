<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Http;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * Some code in this class it taken from zend-diactoros.
 *
 * See the original here: https://github.com/zendframework/zend-diactoros/blob/master/src/ServerRequestFactory.php
 *
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
final class GlobalServerRequest
{
    /**
     * Function to use to get apache request headers; present only to simplify mocking.
     *
     * @var callable
     */
    private static $apacheRequestHeaders = 'apache_request_headers';

    /**
     * Undocumented function.
     *
     * @param ServerRequestInterface $serverRequest
     * @param UriInterface           $uri
     */
    public function __construct(ServerRequestInterface $serverRequest, UriInterface $uri)
    {
        $this->serverRequest = $serverRequest;
        $this->uri           = $uri;
    }

    /**
     * Create a request from the supplied superglobal values.
     *
     * If any argument is not supplied, the corresponding superglobal value will
     * be used.
     *
     * The ServerRequest created is then passed to the fromServer() method in
     * order to marshal the request URI and headers.
     *
     * @param array $server  $_SERVER superglobal
     * @param array $query   $_GET superglobal
     * @param array $body    $_POST superglobal
     * @param array $cookies $_COOKIE superglobal
     * @param array $files   $_FILES superglobal
     *
     * @throws \InvalidArgumentException for invalid file values
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function fromGlobals(
        array $server = null,
        array $query = null,
        array $body = null,
        array $cookies = null,
        array $files = null
    ): ServerRequestInterface {
        $server         = static::normalizeServer($server);
        $marshalHeaders = $this->getHeaders($server);
        $headers        = [];
        $method         = $server['REQUEST_METHOD'] ?? 'GET';

        array_walk($marshalHeaders, function ($value, $key) use (&$headers) {
            $headers[$this->normalizeKey($key)] = $value;
        });

        return $this->serverRequest->withCookieParams($cookies ?? $_COOKIE)
            ->withQueryParams($query ?? $_GET)
            ->withParsedBody($body ?? $_POST)
            ->withUploadedFiles(self::normalizeFiles($files ?? $_FILES));
    }

    /**
     * Return an UploadedFile instance array.
     *
     * @param array $files A array which respect $_FILES structure
     *
     * @throws \InvalidArgumentException for unrecognized values
     *
     * @return array
     */
    public static function normalizeFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);
                continue;
            } else {
                throw new InvalidArgumentException('Invalid value in files specification.');
            }
        }

        return $normalized;
    }

    /**
     * Create and return an UploadedFile instance from a $_FILES specification.
     *
     * If the specification represents an array of values, this method will
     * delegate to normalizeNestedFileSpec() and return that return value.
     *
     * @param array $value $_FILES struct
     *
     * @return array|UploadedFileInterface
     */
    private static function createUploadedFileFromSpec(array $value)
    {
        if (is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }

        return new UploadedFile(
            $value['tmp_name'],
            (int) $value['size'],
            (int) $value['error'],
            $value['name'],
            $value['type']
        );
    }

    /**
     * Normalize an array of file specifications.
     *
     * Loops through all nested files and returns a normalized array of
     * UploadedFileInterface instances.
     *
     * @param array $files
     *
     * @return UploadedFileInterface[]
     */
    private static function normalizeNestedFileSpec(array $files = []): array
    {
        $normalizedFiles = [];

        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
            ];

            $normalizedFiles[$key] = self::createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }

    /**
     * Return HTTP protocol version (X.Y).
     *
     * @param array $server
     *
     * @throws \UnexpectedValueException
     *
     * @return string
     *
     * See the original here: https://github.com/zendframework/zend-diactoros/blob/master/src/ServerRequestFactory.php
     *
     * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     */
    private function marshalProtocolVersion(array $server): string
    {
        if (! isset($server['SERVER_PROTOCOL'])) {
            return '1.1';
        }

        if (! preg_match('#^(HTTP/)?(?P<version>[1-9]\d*(?:\.\d)?)$#', $server['SERVER_PROTOCOL'], $matches)) {
            throw new UnexpectedValueException(sprintf(
                'Unrecognized protocol version (%s)',
                $server['SERVER_PROTOCOL']
            ));
        }

        return $matches['version'];
    }

    /**
     * Get all HTTP header key/values as an associative array for the current request.
     *
     * @param array $server
     *
     * Ported from symfony, see original:
     *
     * @link https://github.com/symfony/symfony/blob/master/src/Symfony/Component/HttpFoundation/ServerBag.php#L28
     *
     * (c) Fabien Potencier <fabien@symfony.com>
     *
     * @return array
     */
    private function getHeaders(array $server): array
    {
        $headers        = [];
        $contentHeaders = [
            'CONTENT_LENGTH' => true,
            'CONTENT_MD5'    => true,
            'CONTENT_TYPE'   => true,
        ];

        foreach ($server as $key => $value) {
            if (mb_substr($key, 0, 5) == 'HTTP_') {
                $headers[$key] = $value;
            // CONTENT_* are not prefixed with HTTP_
            } elseif (isset($contentHeaders[$key])) {
                $headers[$key] = $value;
            }
        }

        if (isset($server['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $server['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW']   = $server['PHP_AUTH_PW'] ?? '';
        } else {
            $authorizationHeader = null;

            if (isset($server['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $server['HTTP_AUTHORIZATION'];
            } elseif (isset($server['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $server['REDIRECT_HTTP_AUTHORIZATION'];
            }

            if ($authorizationHeader !== null) {
                if (mb_stripos($authorizationHeader, 'basic ') === 0) {
                    // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
                    $exploded = explode(':', base64_decode(mb_substr($authorizationHeader, 6)), 2);

                    if (count($exploded) == 2) {
                        list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                    }
                } elseif (empty($server['PHP_AUTH_DIGEST']) && (0 === mb_stripos($authorizationHeader, 'digest '))) {
                    // In some circumstances PHP_AUTH_DIGEST needs to be set
                    $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
                    $server['PHP_AUTH_DIGEST']  = $authorizationHeader;
                } elseif (mb_stripos($authorizationHeader, 'bearer ') === 0) {
                    /*
                     * XXX: Since there is no PHP_AUTH_BEARER in PHP predefined variables,
                     *      I'll just set $headers['AUTHORIZATION'] here.
                     *      http://php.net/manual/en/reserved.variables.server.php
                     */
                    $headers['HTTP_AUTHORIZATION'] = $authorizationHeader;
                }
            }
        }

        if (isset($headers['HTTP_AUTHORIZATION'])) {
            return $headers;
        }

        // PHP_AUTH_USER/PHP_AUTH_PW
        if (isset($headers['PHP_AUTH_USER'])) {
            $headers['HTTP_AUTHORIZATION'] = 'Basic ' . base64_encode($headers['PHP_AUTH_USER'] . ':' . $headers['PHP_AUTH_PW']);
        } elseif (isset($headers['PHP_AUTH_DIGEST'])) {
            $headers['HTTP_AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
        }

        return $headers;
    }

    /**
     * Marshal the $_SERVER array.
     *
     * Pre-processes and returns the $_SERVER superglobal.
     *
     * @param array $server
     *
     * @return array
     *
     * See the original here: https://github.com/zendframework/zend-diactoros/blob/master/src/ServerRequestFactory.php
     *
     * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     */
    private static function normalizeServer(array $server): array
    {
        // This seems to be the only way to get the Authorization header on Apache
        $apacheRequestHeaders = self::$apacheRequestHeaders;

        if (isset($server['HTTP_AUTHORIZATION']) || ! is_callable($apacheRequestHeaders)) {
            return $server;
        }

        $apacheRequestHeaders = $apacheRequestHeaders();

        if (isset($apacheRequestHeaders['Authorization'])) {
            $server['HTTP_AUTHORIZATION'] = $apacheRequestHeaders['Authorization'];

            return $server;
        }

        if (isset($apacheRequestHeaders['authorization'])) {
            $server['HTTP_AUTHORIZATION'] = $apacheRequestHeaders['authorization'];

            return $server;
        }

        return $server;
    }

    /**
     * Normalize header name.
     *
     * This method transforms header names into a
     * normalized form. This is how we enable case-insensitive
     * header names in the other methods in this class.
     *
     * @param string $key The case-insensitive header name
     *
     * @return string Normalized header name
     */
    private function normalizeKey(string $key): string
    {
        $key = strtr(mb_strtolower($key), '_', '-');

        if (mb_strpos($key, 'http-') === 0) {
            $key = mb_substr($key, 5);
        }

        return $key;
    }
}
