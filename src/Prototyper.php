<?php

namespace Concept\Prototype;

use ReflectionObject;

/**
 * Class Prototyper
 *
 * Provides utilities for deep cloning and creating prototypes of objects with optional property and method modifications.
 */
class Prototyper implements PrototyperInterface
{
    /**
     * Perform a deep clone of the given object or value.
     *
     * @param mixed $object The object or value to clone.
     *
     * @return mixed A deep-cloned copy of the input.
     */
    public static function deepClone($object)
    {
        if (is_scalar($object) || is_resource($object) || $object instanceof \Closure || $object instanceof NonPrototypableInterface) {
            return $object;
        }

        if (is_array($object)) {
            return static::deepCloneArray($object);
        }

        if (is_object($object)) {
            return static::cloneObject($object);
        }

        return $object;
    }

    /**
     * Perform a deep clone of an array, recursively cloning objects and arrays within it.
     *
     * @param array $array The array to clone.
     *
     * @return array A deep-cloned copy of the array.
     */
    public static function deepCloneArray(array $array): array
    {
        foreach ($array as $key => $value) {
            $array[$key] = static::deepCloneValue($value);
        }
        return $array;
    }

    /**
     * Clone a single value, performing a deep clone for objects and arrays.
     *
     * @param mixed $value The value to clone.
     *
     * @return mixed A deep-cloned copy of the value.
     */
    private static function deepCloneValue($value)
    {
        if (is_object($value)) {
            return static::deepClone($value);
        }
        if (is_array($value)) {
            return static::deepCloneArray($value);
        }
        return $value;
    }

    /**
     * Clone an object deeply, including its properties, without calling its constructor.
     *
     * @param object $object The object to clone.
     *
     * @return object A deep-cloned copy of the object.
     */
    private static function cloneObject(object $object)
    {
        if ($object instanceof NonPrototypableInterface) {
            return $object;
        }
        if (method_exists($object, '__clone') && !$object instanceof PrototypableInterface) {
            return clone $object;
        }

        $reflection = new ReflectionObject($object);
        $clone = $reflection->newInstanceWithoutConstructor();

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $property->setValue($clone, static::deepClone($property->getValue($object)));
        }

        return $clone;
    }

    /**
     * Create a prototype of the given object with optional property and method modifications.
     *
     * @param object     $object     The object to clone.
     * @param array|null $properties An associative array of properties to set on the cloned object.
     * @param array|null $methods    An associative array of method names and closures to add to the cloned object.
     *
     * @return object The cloned object with the specified properties and methods.
     *
     * @throws \InvalidArgumentException If a property does not exist or a method is invalid.
     */
    public static function createPrototype($object, ?array $properties = null, ?array $methods = null)
    {
        if (empty($properties) && empty($methods)) {
            return static::deepClone($object);
        }

        $prototype = static::deepClone($object);
        $reflection = new ReflectionObject($prototype);

        foreach ($properties ?? [] as $propertyName => $value) {
            if ($reflection->hasProperty($propertyName)) {
                $reflectionProperty = $reflection->getProperty($propertyName);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($prototype, $value);
            } else {
                throw new \InvalidArgumentException(sprintf(
                    "Property '%s' does not exist in class '%s'.",
                    $propertyName,
                    get_class($prototype)
                ));
            }
        }

        foreach ($methods ?? [] as $methodName => $closure) {
            if (!preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $methodName)) {
                throw new \InvalidArgumentException("Invalid method name '$methodName'.");
            }

            if (!($closure instanceof \Closure)) {
                throw new \InvalidArgumentException("Method '$methodName' must be a Closure.");
            }

            $prototype->{$methodName} = $closure->bindTo($prototype, $prototype);
        }

        return $prototype;
    }
}
