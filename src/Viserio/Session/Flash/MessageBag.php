<?php
namespace Viserio\Session\Flash;

class MessageBag extends AutoExpireFlashBag
{
    /**
     * Detailed debug information
     */
    const DEBUG = 'debug';

    /**
     * Interesting events
     */
    const INFO = 'info';

    /**
     * Exceptional occurrences that are not errors
     */
    const WARNING = 'warning';

    /**
     * Runtime errors
     */
    const ERROR = 'error';

    /**
     * Success messages
     */
    const SUCCESS = 'success';

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $storageKey
     */
    public function __construct(string $name = 'messages', string $storageKey = '_v_messages')
    {
        parent::__construct($storageKey);

        $this->setName($name);
    }

    /**
     * Adds debug message
     *
     * @param string $message
     */
    public function debug(string $message)
    {
        $this->add(self::DEBUG, $message);
    }

    /**
     * Adds info message
     *
     * @param string $message
     */
    public function info(string $message)
    {
        $this->add(self::INFO, $message);
    }

    /**
     * Adds warning message
     *
     * @param string $message
     */
    public function warning(string $message)
    {
        $this->add(self::WARNING, $message);
    }

    /**
     * Adds error message
     *
     * @param string $message
     */
    public function error(string $message)
    {
        $this->add(self::ERROR, $message);
    }

    /**
     * Adds success message
     *
     * @param string $message
     */
    public function success(string $message)
    {
        $this->add(self::SUCCESS, $message);
    }
}
