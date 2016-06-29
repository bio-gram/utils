<?php
/**
 * Created by PhpStorm.
 * User: inisire
 * Date: 28.06.16
 * Time: 19:49
 */

namespace PrivateDev\Utils\Error;

class ErrorList implements ErrorListInterface
{
    /**
     * @var ErrorInterface[]
     */
    private $errors = [];

    /**
     * @param ErrorInterface $error
     */
    public function add(ErrorInterface $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return ErrorInterface[]
     */
    public function all() : array
    {
        return $this->errors;
    }
}