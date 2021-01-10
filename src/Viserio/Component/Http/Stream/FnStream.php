<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Http\Stream;

use Psr\Http\Message\StreamInterface;
use Throwable;
use Viserio\Contract\Http\Exception\BadMethodCallException;
use Viserio\Contract\Http\Exception\LogicException;

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
    private const SLOTS = ['__toString', 'close', 'detach', 'rewind',
        'getSize', 'tell', 'eof', 'isSeekable', 'seek', 'isWritable', 'write',
        'isReadable', 'read', 'getContents', 'getMetadata', ];

    /**
     * Create a new fn stream instance.
     *
     * @param array<string, callable> $methods
     */
    public function __construct(array $methods)
    {
        // Create the functions on the class
        foreach ($methods as $name => $fn) {
            $this->{'_fn_' . $name} = $fn;
        }
    }

    /**
     * The close method is called on the underlying stream only if possible.
     *
     * @return void
     */
    public function __destruct()
    {
        if (isset($this->_fn_close)) {
            \call_user_func($this->_fn_close);
        }
    }

    /**
     * Lazily determine which methods are not implemented.
     *
     * @throws \Viserio\Contract\Http\Exception\BadMethodCallException
     */
    public function __get($name): void
    {
        throw new BadMethodCallException(\str_replace('_fn_', '', $name) . '() is not implemented in the FnStream');
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            return \call_user_func($this->_fn___toString);
        } catch (Throwable $exception) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            \trigger_error(self::class . '::__toString exception: ' . (string) $exception, \E_USER_ERROR);

            return '';
        }
    }

    /**
     * An unserialize would allow the __destruct to run when the unserialized value goes out of scope.
     *
     * @throws \Viserio\Contract\Http\Exception\LogicException
     */
    public function __wakeup(): void
    {
        throw new LogicException('FnStream should never be unserialized.');
    }

    /**
     * Adds custom functionality to an underlying stream by intercepting
     * specific method calls.
     *
     * @param \Psr\Http\Message\StreamInterface                                            $stream  Stream to decorate
     * @param array<string, array<int, \Psr\Http\Message\StreamInterface|string>|callable> $methods Hash of method name to a closure
     */
    public static function decorate(StreamInterface $stream, array $methods): self
    {
        // If any of the required methods were not provided, then simply
        // proxy to the decorated stream.
        foreach (\array_diff(self::SLOTS, \array_keys($methods)) as $diff) {
            $methods[$diff] = [$stream, $diff];
        }

        /** @var array<string, callable> $callables */
        $callables = $methods;

        return new self($callables);
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        \call_user_func($this->_fn_close);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        return \call_user_func($this->_fn_detach);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return \call_user_func($this->_fn_getSize);
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return \call_user_func($this->_fn_tell);
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return \call_user_func($this->_fn_eof);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return \call_user_func($this->_fn_isSeekable);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        \call_user_func($this->_fn_rewind);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = \SEEK_SET): void
    {
        \call_user_func($this->_fn_seek, $offset, $whence);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return \call_user_func($this->_fn_isWritable);
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        return \call_user_func($this->_fn_write, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return \call_user_func($this->_fn_isReadable);
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        return \call_user_func($this->_fn_read, $length);
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        return \call_user_func($this->_fn_getContents);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        return \call_user_func($this->_fn_getMetadata, $key);
    }
}
