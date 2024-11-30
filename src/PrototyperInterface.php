<?php
namespace Concept\Prototype;

interface PrototyperInterface
{
    /**
     * Creates a deep clone of the given object.
     * 
     * @param object $object
     * @return object
     */
    static public function deepClone($object);

    /**
     * Creates a deep clone of the given array.
     * 
     * @param array $array
     * @return array
     */
    static public function deepCloneArray(array $array): array;

    /**
     * Creates a prototype of the given object.
     * If properties are provided, they will be set on the new object.
     * If methods are provided, they will be added to the new object.
     * 
     * @param object $object
     * @param array|null $properties
     * @param array|null $methods
     * @return object
     */
    static public function createPrototype($object, ?array $properties = null, ?array $methods = null);
}