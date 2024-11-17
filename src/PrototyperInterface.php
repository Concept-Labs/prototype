<?php
namespace Concept\Prototype;

interface PrototyperInterface
{
    static public function deepClone($object);
    static public function deepCloneArray(array $array): array;
    static public function createPrototype($object, ?array $properties = null, ?array $methods = null);
}