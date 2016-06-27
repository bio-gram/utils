<?php

namespace PrivateDev\Utils\Json;

/**
 * Class JsonApiSerializer
 *
 * @package PrivateDev\Utils\Json
 */
class JsonApiSerializer extends \League\Fractal\Serializer\JsonApiSerializer
{
    /**
    * @param string $resourceKey
    * @param array  $data
    *
    * @return array
    */
    public function item($resourceKey, array $data)
    {
        $resource = [
            'data' => $data,
        ];

        return $resource;
    }
}