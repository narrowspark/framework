<?php
namespace Viserio\Contracts\Session;

interface SessionHandler
{
    public function close ();

    public function destroy (string $session_id);

    public function gc (int $maxlifetime);

    public function open (string $save_path, string $name);

    public function read (string $session_id);

    public function write (string $session_id, string $session_data);
}
