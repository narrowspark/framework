<?php
namespace Viserio\Http;

use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;
use Viserio\Contracts\Http\Response as ResponseContract;
use Viserio\Contracts\Support\Jsonable;
use Viserio\Http\Traits\ResponseParameterTrait;

class JsonResponse extends SymfonyJsonResponse implements ResponseContract
{
    /*
     * Parameter encapsulation
     */
    use ResponseParameterTrait;

    /**
     * The json encoding options.
     *
     * @var int
     */
    protected $jsonOptions;

    /**
     * Constructor.
     *
     * @param mixed $data
     * @param int   $status
     * @param array $headers
     * @param int   $options
     */
    public function __construct($data = null, $status = 200, array $headers = [], int $options = 0)
    {
        $this->jsonOptions = $options;
        parent::__construct($data, $status, $headers);
    }

    /**
     * Get the json_decoded data from the response.
     *
     * @param bool $assoc
     * @param int  $depth
     *
     * @return mixed
     */
    public function getData(bool $assoc = false, $depth = 512)
    {
        return json_decode($this->data, $assoc, $depth);
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data = [])
    {
        $this->data = $data instanceof Jsonable
                                   ? $data->toJson($this->jsonOptions)
                                   : json_encode($data, $this->jsonOptions);

        return $this->update();
    }

    /**
     * Get the JSON encoding options.
     *
     * @return int
     */
    public function getJsonOptions(): int
    {
        return $this->jsonOptions;
    }

    /**
     * Set the JSON encoding options.
     *
     * @param int $options
     *
     * @return SymfonyJsonResponse
     */
    public function setJsonOptions(int $options): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->jsonOptions = $options;

        return $this->setData($this->getData());
    }
}
