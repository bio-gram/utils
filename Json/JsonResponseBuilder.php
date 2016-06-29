<?php

namespace PrivateDev\Utils\Json;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use PrivateDev\Utils\Error\ErrorInterface;
use PrivateDev\Utils\Error\ErrorList;
use PrivateDev\Utils\Error\ErrorListInterface;
use PrivateDev\Utils\Form\FormErrorAdapter;
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
        $this->body = [];
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
     * @param ErrorInterface $error
     *
     * @return $this
     */
    public function addError(ErrorInterface $error)
    {
        $this->body['errors'][] = $this->formatError($error);

        return $this;
    }

    /**
     * @param ErrorListInterface $errorList
     *
     * @return $this
     */
    public function addErrorList(ErrorListInterface $errorList)
    {
        foreach ($errorList->all() as $error) {
            $this->addError($error);
        }

        return $this;
    }

    /**
     * @param ErrorInterface $error
     *
     * @return array
     */
    protected function formatError(ErrorInterface $error)
    {
        if ($error->getCode() > 0) {
            $formatted['code'] = $error->getCode();
        }

        if ($error->getOrigin() != "") {
            $formatted['origin'] = $error->getOrigin();
        }

        $formatted['message'] = $error->getMessage();

        return $formatted;
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