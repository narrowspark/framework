<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Viserio\Component\Routing\AbstractController;

class Controller extends AbstractController
{
    public function string()
    {
        return 'test';
    }
}
