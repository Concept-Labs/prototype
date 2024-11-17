<?php
namespace Concept\Prototype;


class Prototyper implements PrototyperInterface
{

    /**
     * Deep clone an object
     * 
     * @param object $object
     * 
     * @return object
     */
    static public function deepClone($object)
    {
        if ($object instanceof SingletoneInterface || $object instanceof \Closure || is_resource($object) || is_scalar($object)) { 
            return $object;
        }

        if (is_array($object)) {
            return static::deepCloneArray($object);
        }

        $reflection = new \ReflectionObject($object);
        $clone = $reflection->newInstanceWithoutConstructor();

        foreach ($reflection->getProperties() as $property) {
            if (PHP_VERSION_ID < 80100) {
                $property->setAccessible(true);
            }
            $property->setValue($clone, static::deepClone($property->getValue($object)));
        }

        return $clone;
    }

    /**
     * Deep clone an array
     * 
     * @param array $array
     * 
     * @return array
     */
    static public function deepCloneArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $array[$key] = static::deepClone($value);
            }
        }

        return $array;
    }

    /**
     * Creates a prototype of the given object with optional properties and methods.
     *
     * @param object        $object The object to clone.
     * @param array|null    $properties Optional properties to set on the cloned object.
     * @param array|null    $methods Optional methods to add to the cloned object.
     * 
     * @return object       The cloned object with the specified properties and methods.
     * 
     * @throws \InvalidArgumentException If a method is not a closure.
     */
    static public function createPrototype($object, ?array $properties = null, ?array $methods = null)
    {
        
        $prototype = static::deepClone($object);

        foreach ($properties ?? [] as $property => $value) {
            $prototype->$property = $value;
        }

        
        /**
         * @var \Closure $closure
         * The closure that will be added as a method to the cloned object.
         */
        foreach ($methods ?? [] as $method => $closure) {
            if (!is_callable($closure)) {
                throw new \InvalidArgumentException('Method must be a closure');
            }
            $prototype->$method = $closure->bindTo($prototype, get_class($prototype));
        }

        return $prototype;
    }
}