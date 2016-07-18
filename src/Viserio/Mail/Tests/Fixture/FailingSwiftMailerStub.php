<?php
namespace Viserio\Mail\Tests\Fixture;

class FailingSwiftMailerStub
{
    public function send($message, &$failed)
    {
        $failed[] = 'info@narrowspark.de';
    }
}
