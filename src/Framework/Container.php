<?php

declare(strict_types=1);

namespace Framework;

use ReflectionClass, ReflectionNamedType;
use Framework\Exceptions\ContainerException;

class Container
{
  private array $definitions = [];
  private array $resolved = [];

  public function addDefinitions(array $newDefinitions)
  {
    $this->definitions = [...$this->definitions, ...$newDefinitions]; // Can't use spread operator syntax with associative arrays unless PHP 8.1+
  }

  public function resolve(string $className)
  {
    $reflectionClass = new ReflectionClass($className);

    // Check if the class is instantiable because it might be an interface or abstract class which cannot be instantiated
    if (!$reflectionClass->isInstantiable()) {
      throw new ContainerException("Class {$className} is not instantiable");
    }

    $constructor = $reflectionClass->getConstructor();

    if (!$constructor) {
      return new $className;
    }

    $params = $constructor->getParameters();

    if (count($params) === 0) {
      return new $className;
    }

    $dependencies = [];

    foreach ($params as $param) {
      $name = $param->getName();
      $type = $param->getType();

      if (!$type) {
        throw new ContainerException("Failed to resolve class {$className} because parameter {$name} has no type hint");
      }

      if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
        throw new ContainerException("Cannot resolve class {$className} because parameter {$name} is not a class type or is a built-in type");
      }

      $dependencies[] = $this->get($type->getName());
    }

    return $reflectionClass->newInstanceArgs($dependencies);
  }

  public function get(string $id)
  {
    if (!array_key_exists($id, $this->definitions)) {
      throw new ContainerException("Class {$id} does not exist container");
    }

    if (array_key_exists($id, $this->resolved)) {
      return $this->resolved[$id];
    }

    $factory = $this->definitions[$id];
    $dependency = $factory();

    $this->resolved[$id] = $dependency;

    return $dependency;
  }
}
