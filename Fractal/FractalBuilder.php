<?php

namespace Utils\Fractal;

use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;
use Symfony\Component\HttpFoundation\RequestStack;

class FractalBuilder
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * FractalBuilder constructor.
     *
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return Manager
     */
    public function build()
    {
        $request = $this->requestStack->getCurrentRequest();
        $fractal = new Manager();
        $fractal->setSerializer(new JsonApiSerializer($request->getSchemeAndHttpHost()));

        return $fractal;
    }
}