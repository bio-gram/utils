<?php

namespace PrivateDev\Utils\Error;

class Error implements ErrorInterface
{
    /**
     * @var int
     */
    private $code;
    
    /**
     * @var string
     */
    private $origin;
    
    /**
     * @var string
     */
    private $message;

    /**
     * Error constructor.
     *
     * @param int    $code
     * @param string $origin
     * @param string $message
     */
    public function __construct(string $message, int $code = 0, string $origin = "")
    {
        $this->code = $code;
        $this->origin = $origin;
        $this->message = $message;
    }

    public function getCode() : int
    {
        return $this->code;
    }

    public function getOrigin() : string
    {
        return $this->origin;
    }

    public function getMessage() : string
    {
        return $this->message;
    }
}