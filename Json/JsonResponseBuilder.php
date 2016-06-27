<?php

namespace PrivateDev\Utils\Json;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use PrivateDev\Utils\Form\FormErrorDecorator;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\HttpFoundation\JsonResponse;

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
            'error' => []
        ];

        $this->fractal = $fractal;
    }

    public function setData($name, $data)
    {
        $this->body[$name] = $data;
    }

    /**
     * @param $object
     * @param $transformer
     *
     * @return $this
     */
    public function setTranformableData($object, $transformer)
    {
        if (is_array($object)) {
           $item = new Collection($object, $transformer);
        } else {
            $item = new Item($object, $transformer);
        }

        $transformed = $this->fractal
            ->createData($item)
            ->toArray();

        $this->setData('data', $transformed['data']);

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

        $this->setData('error', $decorator->format());

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