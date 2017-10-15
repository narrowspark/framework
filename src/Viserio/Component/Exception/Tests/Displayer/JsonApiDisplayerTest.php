<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Viserio\Component\Exception\Displayer\JsonApiDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\HttpFactory\ResponseFactory;

class JsonApiDisplayerTest extends JsonApiDisplayer
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->displayer = new JsonApiDisplayer(new ExceptionInfo(), new ResponseFactory());
    }
}
