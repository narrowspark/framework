<?php
namespace Viserio\Filesystem\Traits;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

/**
 * MimetypeTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
trait MimetypeTrait
{
    /**
     * Fallback for finfo_file.
     *
     * @var array
     */
    protected $mimetypes = [
        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    ];

    /**
     * Fallback for mimeType.
     *
     * @param string $path
     *
     * @return string|false
     */
    protected function fallbackMimeType($path)
    {
        $explode = explode('.', $path);

        if ($ext = end($explode)) {
            $ext = strtolower($ext);
        }

        return $this->checkType($ext, $path);
    }

    /**
     * Check mime type.
     *
     * @param string $ext
     * @param string $path
     *
     * @return string|false
     */
    protected function checkType($ext, $path)
    {
        if ($this->isImage($path)) {
            $data = getimagesize($path);

            return $data['mime'];
        } elseif ($ext && array_key_exists($ext, $this->mimetypes)) {
            return $this->mimetypes[$ext];
        }

        return false;
    }

    /**
     * Is file an image?
     *
     * @param string $path
     *
     * @return bool
     */
    abstract public function isImage($path);
}
