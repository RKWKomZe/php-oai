<?php
namespace RKW\OaiConnector\Mapper;

use ReflectionClass;
use ReflectionException;

class GenericModelMapper
{
    /**
     * Maps an associative array to a model object via setter methods.
     *
     * @param array $data
     * @param string $modelClass Fully qualified class name
     * @return object
     * @throws ReflectionException
     */
    public static function map(array $data, string $modelClass): object
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("Class $modelClass does not exist.");
        }

        $reflection = new ReflectionClass($modelClass);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));

            if ($reflection->hasMethod($method)) {
                $reflection->getMethod($method)->invoke($instance, $value);
            }
        }

        return $instance;
    }
}
