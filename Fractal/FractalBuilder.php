<?php

namespace PrivateDev\Utils\Fractal;

use League\Fractal\Manager;
use PrivateDev\Utils\Json\JsonApiSerializer;

class FractalBuilder
{
    /**
     * @return Manager
     */
    public function build()
    {
        $fractal = new Manager();
        $fractal->setSerializer(new JsonApiSerializer());

        return $fractal;
    }
}