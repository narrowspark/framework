<?php
namespace Viserio\Http\Stream;

use BadMethodCallException;
use Psr\Http\Message\StreamInterface;

/**
 * Compose stream implementations based on a hash of functions.
 *
 * Allows for easy testing and extension of a provided stream without needing
 * to create a concrete class for a simple extension point.
 */
class FnStream implements StreamInterface
{
    /**
     * Methods that must be implemented in the given array.
     *
     * @var array
     */
    const SLOTS = ['__toString', 'close', 'detach', 'rewind',
        'getSize', 'tell', 'eof', 'isSeekable', 'seek', 'isWritable', 'write',
        'isReadable', 'read', 'getContents', 'getMetadata', ];

    /** @var array */
    private $methods;

    /**
     * @param array $methods Hash of method name to a callable.
     */
    public function __construct(array $methods)
    {
        $this->methods = $methods;

        // Create the functions on the class
        foreach ($methods as $name => $fn) {
            $this->{'_fn_' . $name} = $fn;
        }
    }

    /**
     * The close method is called on the underlying stream only if possible.
     */
    public function __destruct()
    {
        if (isset($this->_fn_close)) {
            call_user_func($this->_fn_close);
        }
    }

    /**
     * Lazily determine which methods are not implemented.
     *
     * @throws \BadMethodCallException
     */
    public function __get($name)
    {
        throw new BadMethodCallException(
            str_replace('_fn_', '', $name) . '() is not implemented in the FnStream'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return call_user_func($this->_fn___toString);
    }

    /**
     * Adds custom functionality to an underlying stream by intercepting
     * specific method calls.
     *
     * @param StreamInterface $stream  Stream to decorate
     * @param array           $methods Hash of method name to a closure
     *
     * @return FnStream
     */
    public static function decorate(StreamInterface $stream, array $methods)
    {
        // If any of the required methods were not provided, then simply
        // proxy to the decorated stream.
        foreach (array_diff(self::SLOTS, array_keys($methods)) as $diff) {
            $methods[$diff] = [$stream, $diff];
        }

        return new self($methods);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return call_user_func($this->_fn_close);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return call_user_func($this->_fn_detach);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return call_user_func($this->_fn_getSize);
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        return call_user_func($this->_fn_tell);
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return call_user_func($this->_fn_eof);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return call_user_func($this->_fn_isSeekable);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        call_user_func($this->_fn_rewind);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        call_user_func($this->_fn_seek, $offset, $whence);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return call_user_func($this->_fn_isWritable);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        return call_user_func($this->_fn_write, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return call_user_func($this->_fn_isReadable);
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        return call_user_func($this->_fn_read, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        return call_user_func($this->_fn_getContents);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        return call_user_func($this->_fn_getMetadata, $key);
    }
}
