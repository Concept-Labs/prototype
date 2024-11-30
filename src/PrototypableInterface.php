<?php
namespace Concept\Prototype;

interface PrototypableInterface
{
    /**
     * Create a new instance of the object
     * 
     * @return mixed
     */
    public function prototype();

    /**
     * Create a deep clone of the object
     * 
     * @return mixed
     */
    public function __clone();
}