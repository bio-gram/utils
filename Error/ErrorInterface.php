<?php

namespace PrivateDev\Utils\Error;

interface ErrorInterface
{
    public function getCode() : int;
    
    public function getOrigin() : string;
    
    public function getMessage() : string;
}