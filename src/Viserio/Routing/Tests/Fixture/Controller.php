<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Fixture;

use Viserio\Routing\AbstractController;

class Controller extends AbstractController
{
    public function string()
    {
        return 'test';
    }
}
