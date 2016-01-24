<?php
namespace Viserio\Filesystem\Traits;

trait MimetypeTrait
{
    /**
     * Fallback for finfo_file.
     *
     * @var array
     */
    protected $mimetypes = [
        'txt'  => 'text/plain',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'php'  => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'swf'  => 'application/x-shockwave-flash',
        'flv'  => 'video/x-flv',

        // images
        'png'  => 'image/png',
        'jpe'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif'  => 'image/tiff',
        'svg'  => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt'  => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai'  => 'application/postscript',
        'eps' => 'application/postscript',
        'ps'  => 'application/postscript',

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
     * Get the mime-type of a given file.
     *
     * @param string $path
     * @param bool   $withencoding
     *
     * @return string|false
     */
    public function mimeType($path, $withencoding = false)
    {
        if (function_exists('finfo_file')) {
            $finfo    = finfo_open($withencoding ? FILEINFO_MIME : FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $path);
            finfo_close($finfo);

            return $mimetype;
        }

        return $this->fallbackMimeType($path);
    }

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
