<?php
namespace Viserio\Contracts\Session;

interface SessionHandler
{
    /**
     * Closes the current session.
     *
     * @return bool
     */
    public function close(): bool;

    /**
     * Destroys a session.
     *
     * @param  string $session_id
     *
     * @return bool
     */
    public function destroy(string $session_id): bool;

    /**
     * Cleans up expired sessions.
     *
     * @param int $maxlifetime
     *
     * @return bool
     */
    public function gc(int $maxlifetime): bool;

    /**
     * Reads the session data from the session storage, and returns the results.
     *
     * @param string $sessionId
     *
     * @return string
     */
    public function read(string $sessionId): string;

    /**
     * Writes the session data to the session storage.
     *
     * @param  string $sessionId
     * @param  string $sessionData
     *
     * @return true
     */
    public function write(string $sessionId, string $sessionData): bool;
}
