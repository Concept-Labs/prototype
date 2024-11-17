<?php
namespace Concept\Prototype;

trait PrototypableTrait
{
    /**
     * Create a new instance of the object
     * 
     * @return mixed
     */
    public function prototype($object = null, ?array $properties = null, ?array $methods = null)
    {
        return Prototyper::createPrototype($object ?? $this, $properties, $methods);
    }

    /**
     * Create a deep clone of the object
     * 
     * @return mixed
     */
    public function __clone()
    {
        return Prototyper::deepClone($this);
    }
    
}