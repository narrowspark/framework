<?php
namespace Viserio\Bus\Tests\Fixture;

class BusDispatcherArgumentMapping
{
    public $flag;
    public $emptyString;

    public function __construct($flag, $emptyString)
    {
        $this->flag = $flag;
        $this->emptyString = $emptyString;
    }

    public function handle()
    {
        return true;
    }
}
