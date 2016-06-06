<?php

namespace Utils\Json;

use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Utils\Form\FormErrorDecorator;

class JsonResponseBuilder
{
    /**
     * @var array
     */
    private $body;

    /**
     * @var Manager
     */
    private $fractal;

    /**
     * JsonResponseBuilder constructor.
     *
     * @param Manager $fractal
     */
    public function __construct(Manager $fractal)
    {
        $this->body = [
            'data' => [],
            'errors' => []
        ];

        $this->fractal = $fractal;
    }

    /**
     * @param $object
     * @param $transformer
     * @param $type
     *
     * @return $this
     */
    public function setData($object, $transformer, $type)
    {
        $item = new Item($object, $transformer, $type);

        $transformed = $this->fractal
            ->createData($item)
            ->toArray();

        $this->body['data'] = $transformed['data'];

        return $this;
    }

    /**
     * @param FormErrorIterator $error
     *
     * @return $this
     */
    public function setError(FormErrorIterator $error)
    {
        $decorator = new FormErrorDecorator($error);

        $this->body['error'] = $decorator->format();

        return $this;
    }

    /**
     * @param int $statusCode
     *
     * @return JsonResponse
     */
    public function build(int $statusCode = 200)
    {
        return new JsonResponse($this->body, $statusCode);
    }
}