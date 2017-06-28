<?php
declare(strict_types=1);
namespace Viserio\Provider\WebServer;

class WebServer
{
    /**
     * @var int
     */
    private const STARTED = 0;

    /**
     * @var int
     */
    private const STOPPED = 1;

    public function start()
    {
        // code...
    }

    public function stop()
    {
        // code...
    }

    public function run()
    {
        // code...
    }

    /**
     * Check if a php build-in web server is running.
     *
     * @param string|null $pidFilePath
     *
     * @return bool
     */
    public function isRunning(string $pidFilePath = null): bool
    {
        $pidFile = $pidFile ?? $this->getDefaultPidFile();

        if (! file_exists($pidFile)) {
            return false;
        }

        $address  = file_get_contents($pidFile);
        $pos      = mb_strrpos($address, ':');
        $hostname = mb_substr($address, 0, $pos);
        $port     = mb_substr($address, $pos + 1);

        if ($fp = @fsockopen($hostname, $port, $errno, $errstr, 1) !== false) {
            fclose($fp);

            return true;
        }

        unlink($pidFile);

        return false;
    }

    /**
     * Get the default pid file path.
     *
     * @return string
     */
    private function getDefaultPidFile(): string
    {
        return getcwd() . '/.web-server-pid';
    }
}
