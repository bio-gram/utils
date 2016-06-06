<?php

namespace Utils\Form;

use Symfony\Component\Form\FormErrorIterator;

/**
 * Class FormErrorDecorator
 * 
 * @package Utils\Form
 */
class FormErrorDecorator
{
    /**
     * @var FormErrorIterator
     */
    private $errorIterator;

    /**
     * FormErrorDecorator constructor.
     *
     * @param FormErrorIterator $errorIterator
     */
    public function __construct(FormErrorIterator $errorIterator)
    {
        $this->errorIterator = $errorIterator;
    }

    /**
     * @return array
     */
    public function format()
    {
        $formatted = [];
        for ($i = 0; $i < $this->errorIterator->count(); $i++) {
            $error = $this->errorIterator->offsetGet($i);
            if ($key = $error->getOrigin()->getName()) {
                $formatted[$key] = $error->getMessage();
            } else {
                $formatted[] = $error->getMessage();
            }
        }
        
        return $formatted;
    }
}